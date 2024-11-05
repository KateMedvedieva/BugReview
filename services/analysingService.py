from flask import Flask, request, jsonify
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error
import numpy as np
import json

app = Flask(__name__)


def series_to_list(series):
    return list(series.reset_index().to_dict(orient='records'))


def unprocessed_defects(df):
    num_defects_to_do = df[df['statusID'] == 3].shape[0]
    total_defects = df.shape[0]

    unprocessed_defects_metric_corrected = num_defects_to_do / total_defects if total_defects > 0 else 0

    def analyze_unprocessed_defects(num_defects_to_do, total_defects):
        if total_defects > 0:
            metric = num_defects_to_do / total_defects
            analysis_text = f"Показник 'Неопрацьовані дефекти' становить {metric:.2f} або {metric * 100:.1f}%. <br />"
            if metric > 0.20:
                analysis_text += "Це перевищує поріг у 20%, що може свідчити про проблеми в команді з розумінням того, що є дефектом, або з пріоритезацією виправлення багів продукту.<br />"
            else:
                analysis_text += "Це менше за поріг у 20%, що свідчить про адекватне управління дефектами в команді.<br />"
        else:
            analysis_text = "Загальна кількість зареєстрованих дефектів дорівнює 0, тому розрахунок показника неможливий.<br />"

        return analysis_text

    analysis_result = analyze_unprocessed_defects(num_defects_to_do, total_defects)
    return analysis_result


def missed_bugs_prod_func(df):
    sprints = df['sprint'].dropna().unique()

    df['createdDate'] = pd.to_datetime(df['createdDate'])
    missed_defects_metrics_updated = {}
    start_date = pd.Timestamp('2023-01-11')

    def calculate_sprint_end_date(start_date):
        return start_date + pd.Timedelta(weeks=2)

    def calculate_missed_defects_for_sprint(sprint_data, sprint_end_date):
        bugs_after_sprint_prod = sprint_data[
            (sprint_data['createdDate'] > sprint_end_date) & (sprint_data['Platform'].str.contains('prod'))]

        total_bugs_sprint = sprint_data.shape[0]

        if total_bugs_sprint > 0:
            missed_defects = bugs_after_sprint_prod.shape[0] / total_bugs_sprint
        else:
            missed_defects = None

        return missed_defects

    for sprint in sprints:
        sprint_data = df[df['sprint'] == sprint]

        end_date = calculate_sprint_end_date(start_date)
        missed_defects_metrics_updated[sprint] = calculate_missed_defects_for_sprint(sprint_data, end_date)

        start_date = end_date

    missed_defects_df = pd.DataFrame(list(missed_defects_metrics_updated.items()),
                                     columns=['sprint', 'metric'])
    missed_defects_df.sort_values(by='sprint', inplace=True)
    missed_defects_df.reset_index(drop=True, inplace=True)

    return missed_defects_df


def generate_missed_bugs_conclusion(missed_defects_df):
    missed_defects_df['sprint'] = missed_defects_df['sprint'].astype(int)
    no_missed_bugs_sprints = missed_defects_df[missed_defects_df['metric'] == 0]['sprint'].tolist()

    average_metric = missed_defects_df['metric'].mean()
    max_missed_bugs = missed_defects_df['metric'].max()
    sprint_with_max_missed_bugs = missed_defects_df[missed_defects_df['metric'] == max_missed_bugs]['sprint'].iloc[0]

    if average_metric < 0.1:
        conclusion = "Середній відсоток пропущених дефектів нижче 10%, що вказує на ефективність процесу тестування.<br />"
    else:
        conclusion = "Середній відсоток пропущених дефектів є більшим ніж 10%, що свідчить про проблеми в процесі тестування.<br />"

    if no_missed_bugs_sprints:
        sprints_str = ', '.join(map(str, no_missed_bugs_sprints))
        conclusion += f" Спринти без пропущених дефектів на прод: {sprints_str}. Процес тестування під час цих спринтів проведений коректно.<br />"

    if max_missed_bugs > 0.1:
        conclusion += f" Увага! Найвища кількість пропущених дефектів ({max_missed_bugs*100:.2f})% спостерігалася у спринті {sprint_with_max_missed_bugs}. Рекомендується звернути особливу увагу на цей спринт.<br />"
    else:
        conclusion += f" Найвища кількість пропущених дефектів у спринті {sprint_with_max_missed_bugs} не перевищує 0.1, що є прийнятним.<br />"

    return conclusion


def average_time_to_solve_func(df):
    total_time_to_solve = df['timeForSolve'].sum()
    total_defects_count = df.shape[0]

    average_time_per_defect = total_time_to_solve / total_defects_count if total_defects_count > 0 else None

    average_time_per_functionality = df.groupby('functionalityID')['timeForSolve'].mean()

    result = {
        "common": {
            "avgTime": average_time_per_defect,
            "status": 1 if average_time_per_defect <= 30 else 0
        },
        "byFunctionality": []
    }

    for functionality_id, time in average_time_per_functionality.items():
        if time > 0:
            result["byFunctionality"].append({
                "id": functionality_id,
                "avgTime": time,
                "status": 1 if time <= 30 else 0
        })

    return result


def analyze_average_time_to_solve(result):
    conclusions = []
    specific_issues = []

    avg_time = result["common"]["avgTime"]
    if avg_time and avg_time <= 30:
        conclusions.append("Загальний середній час життя дефекту в нормі.<br />")
    else:
        conclusions.append("Загальний середній час життя дефекту високий.<br />")

    sorted_functionalities = sorted(result["byFunctionality"], key=lambda x: x["avgTime"], reverse=True)

    for functionality in sorted_functionalities[:5]:
        specific_issues.append(f"Функціональна частина ${functionality['id']}$ має високий час виправлення: {functionality['avgTime']} днів.<br />")

    if specific_issues:
        conclusions.append("Необхідно звернути увагу на функціональні частини з високим часом виправлення дефектів.<br />")
        conclusions.append("Розгляньте можливість впровадження додаткових ресурсів або перегляду стратегій тестування та розробки.<br />")

    return "\n".join(conclusions + specific_issues)


@app.route('/process', methods=['POST'])
def process_data():
    data_string = request.data.decode('utf-8')

    intermediate_data = json.loads(data_string)

    if isinstance(intermediate_data, str):
        final_data = json.loads(intermediate_data)
    else:
        final_data = intermediate_data

    bug_data = pd.DataFrame(final_data)

    by_severity = series_to_list(bug_data['severityID'].value_counts())
    by_priority = series_to_list(bug_data['priorityID'].value_counts())
    by_sprint = series_to_list(bug_data['sprint'].value_counts().sort_index())
    most_bugs_by_functionality = series_to_list(bug_data['functionalityID'].value_counts())

    product_environment = bug_data['Platform'].str.extract(r'(\w+)\((\w+)\)')
    product_environment.columns = ['label', 'environment']
    bug_data_with_env = bug_data.join(product_environment)

    defect_density = series_to_list(bug_data_with_env.groupby(['label', 'environment']).size().unstack().fillna(0))

    missed_bugs_prod = missed_bugs_prod_func(bug_data)

    avg_time_solve = average_time_to_solve_func(bug_data)

    unprocessed_bugs = unprocessed_defects(bug_data)

    missed_bugs_conclusion = generate_missed_bugs_conclusion(missed_bugs_prod)

    analysis_conclusion = analyze_average_time_to_solve(avg_time_solve)

    data_json = {
        "bySeverity": by_severity,
        "byPriority": by_priority,
        "bySprint": by_sprint,
        "mostBugsByFunctionality": most_bugs_by_functionality,
        "defectDensity": defect_density,
        "missedBugs": series_to_list(missed_bugs_prod),
        "missedBugsConclusion": missed_bugs_conclusion,
        "avgResolveTime": avg_time_solve,
        "analysisConclusion": analysis_conclusion,
        "unprocessedBugs": unprocessed_bugs,
    }
    return jsonify(data_json)


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)
