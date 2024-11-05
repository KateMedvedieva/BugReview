from flask import Flask, request, jsonify
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error
import numpy as np
import json
import re

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
            analysis_text = f"The 'Unprocessed Defects' metric is {metric:.2f} or {metric * 100:.1f}%. <br />"
            if metric > 0.20:
                analysis_text += "This exceeds the 20% threshold, which may indicate team problems with understanding what constitutes a defect, or with prioritizing product bug fixes.<br />"
            else:
                analysis_text += "This is below the 20% threshold, indicating adequate defect management in the team.<br />"
        else:
            analysis_text = "The total number of registered defects is 0, so the metric calculation is not possible.<br />"

        return analysis_text

    analysis_result = analyze_unprocessed_defects(num_defects_to_do, total_defects)
    return analysis_result


import pandas as pd

import pandas as pd

def missed_bugs_prod_func(df):

    sprints = sorted(df['sprint'].dropna().unique())

    df['createdDate'] = pd.to_datetime(df['createdDate'])

    missed_defects_metrics_updated = {}

    for i, sprint in enumerate(sprints):

        sprint_data = df[df['sprint'] == sprint]
        total_bugs_sprint = sprint_data.shape[0]

        if total_bugs_sprint == 0:
            missed_defects_metrics_updated[sprint] = 0
            continue

        bugs_after_sprint_prod = df[
            (df['sprint'] > sprint) &
            (df['Platform'].str.contains('prod'))
        ]

        missed_defects = bugs_after_sprint_prod.shape[0] / total_bugs_sprint

        missed_defects_metrics_updated[sprint] = missed_defects

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

    sprint_with_max_missed_bugs = missed_defects_df[
        missed_defects_df['metric'] == max_missed_bugs]['sprint'].iloc[0]

    if average_metric < 0.1:
        conclusion = "The average percentage of missed defects is below 10%, indicating an effective testing process.<br />"
    else:
        conclusion = "The average percentage of missed defects is above 10%, indicating issues in the testing process.<br />"

    if no_missed_bugs_sprints:
        sprints_str = ', '.join(map(str, no_missed_bugs_sprints))
        conclusion += f"Sprints with no missed defects in production: {sprints_str}. The testing process during these sprints was conducted correctly.<br />"

    if max_missed_bugs > 0.1:
        conclusion += f"Warning! The highest number of missed defects ({max_missed_bugs*100:.2f}%) was observed in sprint {sprint_with_max_missed_bugs}. Special attention to this sprint is recommended.<br />"
    else:
        conclusion += f"The highest number of missed defects in sprint {sprint_with_max_missed_bugs} does not exceed 0.1, which is acceptable.<br />"

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
        conclusions.append("Overall average defect lifetime is within normal range.<br />")
    else:
        conclusions.append("Overall average defect lifetime is high.<br />")

    sorted_functionalities = sorted(result["byFunctionality"], key=lambda x: x["avgTime"], reverse=True)

    for functionality in sorted_functionalities[:5]:
        specific_issues.append(f"Functional area ${functionality['id']}$ has a high resolution time: {functionality['avgTime']} days.<br />")

    if specific_issues:
        conclusions.append("Attention needed for functional areas with high defect resolution times.<br />")
        conclusions.append("Consider implementing additional resources or reviewing testing and development strategies.<br />")

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
