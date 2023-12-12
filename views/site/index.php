<?php

use app\models\Functionality;
use dosamigos\chartjs\ChartJs;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;

/** @var app\models\Filter $model */
/** @var yii\web\View $this */
/** @var ActiveForm $form */

$this->title = 'Dashboard';
?>

<div class="site-index">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div class="body-content">
        <?php
        $form = ActiveForm::begin([
            'action' => Url::to(['site/index']),
            'method' => 'get',
        ]);
        ?>


        <div class="d-flex justify-content-between align-items-center mb-5">
            <?= $form->field($model, 'startDate', ['options' => ['class' => 'w-100 mr-5']])->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control'],
            ]) ?>
            <?= $form->field($model, 'endDate', ['options' => ['class' => 'w-100 mr-5']])->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'yyyy-MM-dd',
                'options' => ['class' => 'form-control'],
            ]) ?>
            <?= Html::submitButton('Filter', ['class' => 'btn btn-primary', 'style' => 'margin-top: 32px'])?>
        </div>

        <?php ActiveForm::end() ?>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboards-tab" data-bs-toggle="tab" data-bs-target="#dashboards" type="button" role="tab" aria-controls="dashboards" aria-selected="true">Dashboards</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button" role="tab" aria-controls="metrics" aria-selected="false">Metrics</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="conclusion-tab" data-bs-toggle="tab" data-bs-target="#conclusion" type="button" role="tab" aria-controls="conclusion" aria-selected="false">Conclusions</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="dashboards" role="tabpanel" aria-labelledby="dashboards-tab">
                <?php if($data): ?>
                    <div class="d-flex w-100">
                        <div class="d-flex flex-column w-100 mr-5">
                            <h5> Distribution of Bugs by Severity  </h5>
                            <?php
                            $priorityData = array_map(function ($item) {
                                return $item['count'];
                            }, $data['bySeverity']);
                            $priorityLabels = array_map(function ($item) {
                                return \app\models\Severity::findOne(['id' => $item['severityID']])['name'];
                            }, $data['bySeverity']);
                            $colors = ['#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1'];

                            $colors = array_slice(array_merge($colors, $colors), 0, count($priorityData));
                            $totalBugs = array_sum($priorityData);
                            $highImpactBugsCount = 0;
                            $highImpactLabels = ['1 - Critical', '2 - High'];

                            foreach ($priorityLabels as $index => $label) {
                                if (in_array($label, $highImpactLabels)) {
                                    $highImpactBugsCount += $priorityData[$index];
                                }
                            }

                            $highImpactPercentage = ($highImpactBugsCount / $totalBugs) * 100;
                                $analysisTextSeverity = "<h5>Аналіз помилок за серйозністю:</h5>";

                            foreach ($priorityData as $index => $count) {
                                $label = $priorityLabels[$index];
                                $percentage = ($count / $totalBugs) * 100;
                                $analysisTextSeverity .= "<p>$label: $count помилок (" . round($percentage, 2) . "% від загальної кількості)</p>";
                            }

                            if ($highImpactPercentage > 20) {
                                $analysisTextSeverity .= "<p><strong>Помилки з високим ступенем впливу:</strong> $highImpactBugsCount помилок (" . round($highImpactPercentage, 2) . "% від загальної кількості). Це критично та потребує негайного реагування.</p>";
                            } else {
                                $analysisTextSeverity .= "<p><strong>Помилки з високим ступенем впливу:</strong> $highImpactBugsCount помилок (" . round($highImpactPercentage, 2) . "% від загальної кількості). Це в межах припустимого діапазону, але рекомендується моніторинг.</p>";
                            }

                            ?>

                            <?= ChartJs::widget([
                                'type' => 'bar',
                                'data' => [
                                    'labels' => $priorityLabels,
                                    'datasets' => [
                                        [
                                            'label' => "Bugs Count",
                                            'data' => $priorityData,
                                            'backgroundColor' => $colors
                                        ]
                                    ]
                                ],
                                'options' => [
                                    'plugins' => [
                                        'legend' => [
                                            'display' => true,
                                        ],
                                    ],
                                ]
                            ]) ?>

                        </div>

                        <div class="d-flex flex-column w-100">
                            <h5> Distribution of Bugs by Priority  </h5>
                            <?php
                            $priorityData = array_map(function ($item) {
                                return $item['count'];
                            }, $data['byPriority']);

                            $priorityLabels = array_map(function ($item) {
                                return \app\models\Priority::findOne(['id' => $item['priorityID']])['name'];
                            }, $data['byPriority']);

                            $colors = ['#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1'];

                            $colors = array_slice(array_merge($colors, $colors), 0, count($priorityData));

                            $totalBugs = array_sum($priorityData);
                            $analysisTextPriority = "<h5>Аналіз помилок за пріоритетністю:</h5>";

                            $priorityIndexes = array_flip($priorityLabels);

                            foreach ($priorityIndexes as $priorityName => $index) {
                                if (array_key_exists($index, $priorityData)) {
                                    $count = $priorityData[$index];
                                    $percentage = ($count / $totalBugs) * 100;
                                    $analysisTextPriority .= "<p><b>Пріоритет $priorityName:</b> $count помилок (" . round($percentage, 2) . "% від загальної кількості)</p>";
                                }
                            }

                            if ($priorityData[$priorityIndexes['1 - Critical']] > 0) {
                                $analysisTextPriority .= "<p><b>Потребує негайного втручання:</b> Є " . $priorityData[$priorityIndexes['Critical']] . " критичних помилок, які потребують негайного вирішення.</p>";
                            } else {
                                $analysisTextPriority .= "<p><b>Всі критичні помилки під контролем:</b> Немає критичних помилок, що свідчить про стабільне становище продукту.</p>";
                            }

                            if ($priorityData[$priorityIndexes['2 - High']] > 0) {
                                $analysisTextPriority .= "<p><b>Проблеми високого пріоритету:</b> Є " . $priorityData[$priorityIndexes['High']] . " помилок високого пріоритету, які слід невідкладно вирішити, щоб уникнути значного впливу на якість продукту.</p>";
                            }

                            $recommendationsPriority = "";
                            if ($totalBugs > 0) {
                                if ($priorityData[$priorityIndexes['1 - Critical']] > 0 || $priorityData[$priorityIndexes['High']] > 0) {
                                    $recommendationsPriority .= "Потрібна негайна дія для усунення критичних та помилок високого пріоритету. ";
                                }
                                if ($priorityData[$priorityIndexes['3 - Medium']] > 0) {
                                    $recommendationsPriority .= "Помилки середнього пріоритету слід запланувати до усунення у відповідності з графіком проекту. ";
                                }
                            } else {
                                $recommendationsPriority .= "Помилок немає - чудова робота з підтримки якості!";
                            }

                            ?>


                            <?= ChartJs::widget([
                                'type' => 'bar',
                                'data' => [
                                    'labels' => $priorityLabels,
                                    'datasets' => [
                                        [
                                            'label' => 'Bugs Count',
                                            'data' => $priorityData,
                                            'backgroundColor' => $colors,
                                        ]
                                    ]
                                ],
                                'options' => [
                                    'plugins' => [
                                        'datalabels' => [
                                            'anchor' => 'end',
                                            'align' => 'top',
                                            'formatter' => new \yii\web\JsExpression('function(value, context) {
                                                            return value;
                                                           }'),
                                            'color' => '#444',
                                        ]
                                    ]
                                ],
                            ])
                            ?>

                            </div>
                        </div>
                    <div class="d-flex flex-column w-100 mr-5 h-200px">
                        <h5> Trends in Bug Discovery by Sprint  </h5>

                        <?php
                        $bySprintData = array_map(function ($item) {
                            return $item['count'];
                        }, $data['bySprint']);
                        if (!in_array(0, $bySprintData)) {
                            $bySprintData[] = 0;
                        }
                        $bySprintLabels = array_map(function ($item) {
                            return $item['sprint'];
                        }, $data['bySprint']);
                        $colors = ['#00876c', '#66c2a4', '#abdda4', '#e6f598', '#fee08b', '#fdae61', '#f46d43', '#d53e4f', '#9e0142', '#5e4fa2'];

                        $colors = array_slice(array_merge($colors, $colors), 0, count($bySprintData));

                        function linearRegression($x, $y) {
                            $n = count($x);
                            $x_sum = array_sum($x);
                            $y_sum = array_sum($y);
                            $xx_sum = 0;
                            $xy_sum = 0;

                            for($i = 0; $i < $n; $i++){
                                $xy_sum += ($x[$i]*$y[$i]);
                                $xx_sum += ($x[$i]*$x[$i]);
                            }

                            $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
                            $intercept = ($y_sum - ($slope * $x_sum)) / $n;

                            return [$slope, $intercept];
                        }

                        list($slope, $intercept) = linearRegression($bySprintLabels, $bySprintData);

                        $trendData = [];
                        foreach ($bySprintLabels as $i => $label) {
                            $trendData[] = $slope * $i + $intercept;
                        }

                        $increase = false;
                        if ($slope > 0) {
                            $increase = true;
                        }

                        $analysisSummary = "<h5>Підсумки аналізу тенденцій:</h5>";
                        $recommendationsTrend = "<h5>Рекомендації:</h5>";

                        if ($increase) {
                            $analysisSummary .= "<p>Лінія тренду вказує на збільшення кількості помилок за спринтами. Це може свідчити про те, що нові функції або зміни в коді вводять помилки.</p>";
                            $recommendationsTrend .= "<p>Перегляньте нещодавні зміни в кодовій базі та покращіть охоплення тестуванням. Розгляньте можливість переоцінки процесів розробки та контролю якості для виявлення потенційних прогалин.</p>";
                        } else {
                            $analysisSummary .= "<p>Лінія тренду вказує на стабільну або зменшуючу кількість помилок за спринтами. Це свідчить про те, що команда ефективно керує помилками та усуває їх по мірі виникнення.</p>";
                            $recommendationsTrend  .= "<p>Продовжуйте використовувати поточні стратегії управління помилками та зосередьтеся на тих областях, де помилки все ще звітуються, для подальшого покращення якості.</p>";
                        }

                        ?>

                        <?= ChartJs::widget([
                            'type' => 'line',
                            'options' => [
                                'height' => 200,
                                'responsive' => true,
                            ],
                            'data' => [
                                'labels' => $bySprintLabels,
                                'datasets' => [
                                    [
                                        'label' => "Trend Line",
                                        'data' => $trendData,
                                        'borderColor' => 'rgba(255, 99, 132, 1)',
                                        'backgroundColor' => 'rgba(0, 0, 0, 0)',
                                        'type' => 'line',
                                        'fill' => false,
                                        'borderWidth' => 2,
                                        'pointRadius' => 0,
                                        'yAxisID' => 'y-axis-2',
                                    ],
                                    [
                                        'label' => "Bugs Found",
                                        'data' => $bySprintData,
                                        'backgroundColor' => $colors,
                                        'borderColor' => $colors,
                                        'fill' => false,
                                        'type' => 'line',
                                        'yAxisID' => 'y-axis-1',
                                    ]
                                ]
                            ],
                            'clientOptions' => [
                                'scales' => [
                                    'yAxes' => [
                                        [
                                            'id' => 'y-axis-1',
                                            'type' => 'linear',
                                            'position' => 'left',
                                        ],
                                        [
                                            'id' => 'y-axis-2',
                                            'type' => 'linear',
                                            'position' => 'right',
                                            'gridLines' => [
                                                'drawOnChartArea' => false,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]) ?>


                    </div>

                    <div class="d-flex flex-column w-100 mr-5">
                        <h5> Functionalities with Most bugs  </h5>
                        <?php
                        $bySprintData = array_map(function ($item) {
                            return $item['count'];
                        }, $data['mostBugsByFunctionality']);
                        if (!in_array(0, $bySprintData)) {
                            $bySprintData[] = 0;
                        }
                        $bySprintLabels = array_map(function ($item) {
                            return \app\models\Functionality::findOne(['id' => $item['functionalityID']])['name'];
                        }, $data['mostBugsByFunctionality']);
                        $colors = [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#00876c', '#66c2a4',
                            '#abdda4', '#e6f598', '#fee08b', '#fdae61', '#f46d43', '#d53e4f',
                            '#9e0142', '#5e4fa2', '#3288bd', '#66c2a4', '#abdda4', '#e6f598',
                            '#ffffbf', '#fee08b', '#fdae61', '#f46d43', '#d53e4f', '#9e0142',
                            '#5e4fa2', '#3288bd', '#66c2a4', '#abdda4', '#e6f598', '#ffffbf',
                            '#fee08b', '#fdae61', '#f46d43', '#d53e4f', '#9e0142', '#5e4fa2',
                            '#3288bd', '#66c2a4', '#abdda4', '#e6f598', '#ffffbf', '#fee08b',
                            '#fdae61', '#f46d43', '#d53e4f', '#9e0142', '#5e4fa2', '#3288bd',
                            '#66c2a4', '#abdda4', '#e6f598', '#ffffbf', '#fee08b', '#fdae61',
                            '#f46d43', '#d53e4f', '#9e0142', '#5e4fa2', '#3288bd', '#66c2a4',
                            '#abdda4', '#e6f598', '#ffffbf', '#fee08b', '#fdae61', '#f46d43',
                            '#d53e4f', '#9e0142', '#5e4fa2', '#3288bd', '#66c2a4', '#abdda4',
                            '#e6f598', '#ffffbf', '#fee08b', '#fdae61', '#f46d43', '#d53e4f',
                            '#9e0142', '#5e4fa2', '#3288bd'
                        ];

                        $colors = array_slice(array_merge($colors, $colors), 0, count($bySprintData));
                        $analysisTextFunctionality = "<h5>Аналіз топ-5 функціональностей за кількістю помилок:</h5>";
                        $recommendationsFunctionalities = "<h5>Рекомендації:</h5>";


                        $urgentThreshold = 20;
                        $attentionThreshold = 15;
                        $topFunctionalities = array_slice($data['mostBugsByFunctionality'], 0, 5);

                        $urgentFixes = [];
                        $needsAttention = [];

                        foreach ($topFunctionalities as $functionality) {
                            $funcName = \app\models\Functionality::findOne(['id' => $functionality['functionalityID']])['name'];
                            $bugsCount = $functionality['count'];

                            if ($bugsCount >= $urgentThreshold) {
                                $urgentFixes[] = $funcName;
                            } elseif ($bugsCount >= $attentionThreshold) {
                                $needsAttention[] = $funcName;
                            }
                            $percentageOfTotal = ($bugsCount / $totalBugs) * 100;
                            $analysisTextFunctionality .= "<p><b>{$funcName}:</b> {$bugsCount} помилок, що становить " . round($percentageOfTotal, 2) . "% від загальної кількості.</p>";
                        }

                        if (!empty($urgentFixes)) {
                            $funcNames = implode(', ', $urgentFixes);
                            $recommendationsFunctionalities .= "<p>Функціональність {$funcNames} має значну кількість помилок, що вимагає негайного аналізу та вирішення.</p>";
                        }

                        if (!empty($needsAttention)) {
                            $funcNames = implode(', ', $needsAttention);
                            $recommendationsFunctionalities .= "<p>Функціональність {$funcNames} містить помітну кількість помилок і потребує уваги для покращення.</p>";
                        }

                        if (empty($urgentFixes) && empty($needsAttention)) {
                            $recommendationsFunctionalities .= "<p>Всі функціональності знаходяться в задовільному стані. Продовжуйте моніторинг та уважно ставтеся до нових помилок.</p>";
                        }

                        ?>

                        <?= ChartJs::widget([
                            'type' => 'horizontalBar',
                            'options' => [
                                'height' => 1000,
                            ],
                            'data' => [
                                'labels' => $bySprintLabels,
                                'datasets' => [
                                    [
                                        'label' => '',
                                        'data' => $bySprintData,
                                        'backgroundColor' => $colors
                                    ]
                                ]
                            ]
                        ]) ?>


                    </div>

                    <div class="d-flex flex-column w-100 mr-5">
                        <h5> Defect Density  </h5
                            <?php

                            $dev = [];
                            $prod = [];
                            $defectDensityData = [];
                            foreach ($data['defectDensity'] as $i => $item) {
                                $dev[] = $item['dev'];
                                $prod[] = -$item['prod'];
                                $defectDensityData[] = [
                                    'label' => $item['label'],
                                    'data' => [$item['dev'], - $item['prod']],
                                    'borderColor' => $colors[$i % count($colors)],
                                ];
                            }

                            $defectDensityLabels = array_map(function ($item) {
                                return $item['label'];
                            }, $data['defectDensity']);


                            $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#00876c', '#66c2a4'];

                            $colors = array_slice(array_merge($colors, $colors), 0, count($defectDensityData));
                            foreach ($defectDensityData as $key => $dataset) {
                                $defectDensityData[$key]['backgroundColor'] = $colors;
                            }

                            $analysisTextDensity = "<h5>Аналіз щільності дефектів за платформами та середовищами:</h5>";

                            $devDefectsByPlatform = [];
                            $prodDefectsByPlatform = [];


                            foreach ($defectDensityData as $d) {
                                $platform = htmlspecialchars($d['label'], ENT_QUOTES, 'UTF-8');
                                $prodDefects = $d['data'][1] * -1;
                                $devDefects = $d['data'][0];
                                $totalDefects = $devDefects + $prodDefects;

                                $analysisTextDensity .= "<p>Платформа <b>{$platform}</b>:</p>";
                                $analysisTextDensity .= "<ul>";
                                $analysisTextDensity .= "<li>Dev середовище: {$devDefects} дефектів.</li>";
                                $analysisTextDensity .= "<li>Prod середовище: {$prodDefects} дефектів.</li>";
                                $analysisTextDensity .= "<li>Загальна кількість дефектів: " . ($devDefects + $prodDefects) . ".</li>";
                                $analysisTextDensity .= "</ul>";
                            }

                            $recommendationsDensity = "<h5>Рекомендації:</h5>";

                            $percentDev = 0.80;
                            $percentProd = 0.20;

                                $expectedDevDefects = $totalDefects * $percentDev;
                                $expectedProdDefects = $totalDefects * $percentProd;

                                if ($devDefects > $expectedDevDefects) {
                                    $recommendationsDensity .= "<p>Платформа <b>{$platform}</b> у dev середовищі має більшу, ніж очікувалося, кількість дефектів. Рекомендується провести ретельний аналіз причин дефектів та оптимізувати процеси розробки та тестування.</p>";
                                }

                                if ($prodDefects > $expectedProdDefects) {
                                    $recommendationsDensity .= "<p>Платформа <b>{$platform}</b> у prod середовищі має більшу, ніж очікувалося, кількість дефектів. Необхідно негайно вжити заходів для виправлення та запобігання виникненню критичних помилок.</p>";
                                }
                            ?>

                                <!--                    --><?php //= print_r($defectDensityData) ?>

                            <div class="d-flex flex-row">
                                <div
                                    class="d-flex flex-column justify-content-around"
                                    style="left: 22px; position: relative; z-index: 11"
                                >
                                    <div
                                        style="background: #36A2EB; width: 70px; color: white; font-weight: 500;"
                                        class="h-50 p-2 d-flex justify-content-center align-items-center"
                                    >
                                        Dev
                                    </div>
                                    <div
                                        style="background: #FF6384; width: 70px; color: white; font-weight: 500;"
                                        class="h-50 p-2 d-flex justify-content-center align-items-center"
                                    >
                                        Prod
                                    </div>
                                </div>
                                <div style="width: calc(100% - 106px); position: relative; left: -12px; z-index: 10">
                                <?= ChartJs::widget([
                                    'type' => 'bar',
                                    'options' => [
                                        'style' => "",
                                        'height' => 200,
                                        'responsive' => true,
                                        'interaction' => [
                                          'intersect' => false
                                        ],
                                        'scales' => [
                                            'xAxes' => [[
                                                'stacked' => true
                                            ]],
                                            'yAxes' => [[
                                                'stacked' => true
                                            ]]
                                        ],
                                        'plugins' => [
                                            'legend' => ['display' => false,]
                                        ]
                                    ],
                                    'data' => [
                                        'labels' => $defectDensityLabels,
                                        'datasets' => [
                                            [
                                                'label' => 'Dev',
                                                'data' => $dev,
                                                'backgroundColor' => '#36A2EB',
                                            ],
                                            [
                                                'label' => 'Prod',
                                                'data' => $prod,
                                                'backgroundColor' => '#FF6384',
                                            ]
                                        ]
                                    ]
                                ]) ?>
                                </div>
                                <div
                                    class="d-flex flex-column justify-content-around"
                                    style="right: 14px; position: relative;"
                                >
                                    <div
                                            style="background: #36A2EB; width: 70px; color: white; font-weight: 500;"
                                            class="h-50 p-2 d-flex justify-content-center align-items-center"
                                    >
                                        Dev
                                    </div>
                                    <div
                                            style="background: #FF6384; width: 70px; color: white; font-weight: 500;"
                                            class="h-50 p-2 d-flex justify-content-center align-items-center"
                                    >
                                        Prod
                                    </div>
                                </div>
                            </div>

                    </div>
                <?php else: ?>
                    <div class="text m-5"> No data found </div>
                <?php endif; ?>
            </div>
            <div class="tab-pane" id="metrics" role="tabpanel" aria-labelledby="metrics-tab">
                <?php if($data): ?>
                    <div class="d-flex justify-content-between flex-row align-items-baseline mt-1" style="gap: 20px">
                        <table class="table" style="width: 30%">
                            <thead class="thead-dark">
                            <tr>
                                <th> Sprint </th>
                                <th> Missed bugs </th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                                foreach ($data['missedBugs'] as $metric) {
                                echo "<tr>";
                                echo "<td class='p-1'>" . $metric['sprint'] . "</td>";
                                echo "<td class='p-1'>" . round($metric['metric']*100,2) . "%</td>";
                                echo "</tr>";

                            }
                            ?>
                            </tbody>
                        </table>

                        <table class="table" style="width: 65%">
                            <thead class="thead-dark">
                            <tr>
                                <th colspan="3" class="text-center"> Average spend time </th>
                                <th colspan="3" class="text-center"> Trend </th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr>
                                <?php $status = $data['avgResolveTime']['common']['status'] ?>
                                <td class='text-center' colspan="3"><?= $data['avgResolveTime']['common']['avgTime'] . ' days' ?></td>
                                <td class='text-center' style="background: <?= $status == 1 ? 'green' : 'red' ?>" colspan="3"><?= $status == 1 ? 'Good' : 'Bad' ?></td>
                            </tr>
                            </tbody>

                            <thead class="thead-dark">
                            <tr>
                                <th colspan="2"> Functionality </th>
                                <th colspan="2" class="text-center"> Amount </th>
                                <th colspan="2" class="text-center"> Trend </th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            foreach ($data['avgResolveTime']['byFunctionality'] as $d) {
                                $status = $d['status'];
                                $status_color = $status == 1 ? "green" : 'red';
                                $functionalityEntity = \app\models\Functionality::findOne(['id' => $d['id']]);

                                echo "<tr>";
                                echo "<td colspan='2'>" . $functionalityEntity['name'] . "</td>";
                                echo "<td colspan='2'>" . $d['avgTime'] . ' days'."</td>";
                                echo "<td class='text-center' colspan='2' style='background: ".$status_color."' colspan='2'>";
                                echo $status == 1 ? 'Good' : 'Bad';
                                echo "</td>";
                                echo "</tr>";

                            }
                            ?>
                            </tbody>
                        </table>

                    </div>

                <?php else: ?>
                    <div class="text m-5"> No data found </div>
                <?php endif; ?>
            </div>
            <div class="tab-pane" id="conclusion" role="tabpanel" aria-labelledby="conclusion-tab">
                <?php if($data): ?>


                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Analysis of Bugs by Severity:</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisTextSeverity;?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Analysis of Bugs by Priority:</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisTextPriority;
                                echo "<h5>Рекомендації:</h5>";
                                echo "<p>$recommendationsPriority</p>";?>
                        </div>
                    </div>

                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Trends in Bug Discovery by Sprint:</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisSummary;
                                echo $recommendationsTrend;
                                ?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Functionalities with Most bugs :</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?=  $analysisTextFunctionality;
                                echo $recommendationsFunctionalities;
                                ?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Defect Density :</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?=  $analysisTextDensity;
                                echo $recommendationsDensity;
                                ?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header"> Unprocessed Bugs:</div>
                        <div class="card-body">
                            <?= $data['unprocessedBugs'] ?>
                        </div>
                    </div>

                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Defects Leakage:</div>
                        <div class="card-body">
                            <?= $data['missedBugsConclusion'] ?>
                        </div>
                    </div>

                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Average lifetime of defects:</div>
                        <div class="card-body">
                            <?php
                            $result = preg_replace_callback('/\$(\d+(\.\d+)?)\$/', function($matches) {
                                $id = $matches[1];
                                $name = Functionality::findOne(['id' => $id])['name'];
                                return "<b>".$name."</b>";
                            }, $data['analysisConclusion']);
                           echo $result ?>
                        </div>
                    </div>


                <?php else: ?>
                    <div class="text m-5"> No data found </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
