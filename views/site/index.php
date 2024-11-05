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
            <?= Html::submitButton('Filter', ['class' => 'btn btn-primary', 'style' => 'margin-top: 32px']) ?>
            <?php ActiveForm::end() ?>

            <?= Html::beginForm(Url::to(['site/index']), 'get'); ?>

            <?= Html::submitButton('Clear', ['class' => 'btn btn-secondary ml-2', 'style' => 'margin-top: 32px']) ?>

            <?= Html::endForm(); ?>
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboards-tab" data-bs-toggle="tab" data-bs-target="#dashboards"
                        type="button" role="tab" aria-controls="dashboards" aria-selected="true">Dashboards
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button"
                        role="tab" aria-controls="metrics" aria-selected="false">Metrics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="conclusion-tab" data-bs-toggle="tab" data-bs-target="#conclusion"
                        type="button" role="tab" aria-controls="conclusion" aria-selected="false">Conclusions
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="dashboards" role="tabpanel" aria-labelledby="dashboards-tab">
                <?php if ($data): ?>
                    <div class="d-flex w-100">
                        <div class="d-flex flex-column w-100 mr-5">
                            <h5> Distribution of Bugs by Severity </h5>
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
                            $analysisTextSeverity = "<h5>Bug Severity Analysis:</h5>";

                            foreach ($priorityData as $index => $count) {
                                $label = $priorityLabels[$index];
                                $percentage = ($count / $totalBugs) * 100;
                                $analysisTextSeverity .= "<p>$label: $count bugs (" . round($percentage, 2) . "% of total)</p>";
                            }

                            if ($highImpactPercentage > 20) {
                                $analysisTextSeverity .= "<p><strong>High-impact bugs:</strong> $highImpactBugsCount bugs (" . round($highImpactPercentage, 2) . "% of total). This is critical and requires immediate attention.</p>";
                            } else {
                                $analysisTextSeverity .= "<p><strong>High-impact bugs:</strong> $highImpactBugsCount bugs (" . round($highImpactPercentage, 2) . "% of total). This is within acceptable range but should be monitored.</p>";
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
                            <h5> Distribution of Bugs by Priority </h5>
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
                            $analysisTextPriority = "<h5>Bug Priority Analysis:</h5>";

                            $priorityIndexes = array_flip($priorityLabels);

                            foreach ($priorityIndexes as $priorityName => $index) {
                                if (array_key_exists($index, $priorityData)) {
                                    $count = $priorityData[$index];
                                    $percentage = ($count / $totalBugs) * 100;
                                    $analysisTextPriority .= "<p><b>Priority $priorityName:</b> $count bugs (" . round($percentage, 2) . "% of total)</p>";
                                }
                            }

                            if (isset($priorityIndexes['1 - Critical']) && $priorityData[$priorityIndexes['1 - Critical']] > 0) {
                                $analysisTextPriority .= "<p><b>Requires immediate intervention:</b> There are " . $priorityData[$priorityIndexes['1 - Critical']] . " critical bugs that need immediate resolution.</p>";
                            } else {
                                $analysisTextPriority .= "<p><b>All critical bugs under control:</b> No critical bugs present, indicating stable product status.</p>";
                            }

                            if (isset($priorityIndexes['2 - High']) && $priorityData[$priorityIndexes['2 - High']] > 0) {
                                $analysisTextPriority .= "<p><b>High-priority issues:</b> There are " . $priorityData[$priorityIndexes['2 - High']] . " high-priority bugs that should be addressed promptly to avoid significant impact on product quality.</p>";
                            }

                            $recommendationsPriority = "";
                            if ($totalBugs > 0) {
                                if ((isset($priorityIndexes['1 - Critical']) && $priorityData[$priorityIndexes['1 - Critical']]) > 0
                                    || (isset($priorityIndexes['2 - High']) && $priorityData[$priorityIndexes['2 - High']] > 0)) {
                                    $recommendationsPriority .= "Immediate action required to address critical and high-priority bugs. ";
                                }
                                if ($priorityData[$priorityIndexes['3 - Medium']] > 0) {
                                    $recommendationsPriority .= "Medium-priority bugs should be scheduled for resolution according to the project timeline. ";
                                }
                            } else {
                                $recommendationsPriority .= "No bugs present - excellent quality maintenance!";
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
                        <h5> Trends in Bug Discovery by Sprint </h5>

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

                        function linearRegression($x, $y)
                        {
                            $n = count($x);
                            $x_sum = array_sum($x);
                            $y_sum = array_sum($y);
                            $xx_sum = 0;
                            $xy_sum = 0;

                            for ($i = 0; $i < $n; $i++) {
                                $xy_sum += ($x[$i] * $y[$i]);
                                $xx_sum += ($x[$i] * $x[$i]);
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

                        $analysisSummary = "<h5>Trend Analysis Summary:</h5>";
                        $recommendationsTrend = "<h5>Recommendations:</h5>";

                        if ($increase) {
                            $analysisSummary .= "<p>The trend line indicates an increase in bug count across sprints. This may suggest that new features or code changes are introducing bugs.</p>";
                            $recommendationsTrend .= "<p>Review recent codebase changes and improve test coverage. Consider re-evaluating development and quality control processes to identify potential gaps.</p>";
                        } else {
                            $analysisSummary .= "<p>The trend line shows stable or decreasing bug counts across sprints. This indicates that the team is effectively managing and resolving bugs as they arise.</p>";
                            $recommendationsTrend .= "<p>Continue using current bug management strategies and focus on areas where bugs are still being reported for further quality improvement.</p>";
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
                        <h5> Functionalities with Most bugs </h5>
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
                        $analysisTextFunctionality = "<h5>Top 5 Functionalities by Bug Count:</h5>";
                        $recommendationsFunctionalities = "<h5>Recommendations:</h5>";


                        $urgentThreshold = 20;
                        $attentionThreshold = 15;
                        $topFunctionalities = array_slice($data['mostBugsByFunctionality'], 0, 5);

                        $urgentFixes = [];
                        $needsAttention = [];

                        foreach ($topFunctionalities as $functionality) {
                            $funcName = \app\models\Functionality::findOne(['id' => $functionality['functionalityID']])['name'];
                            $bugsCount = $functionality['count'];
                            $percentageOfTotal = ($bugsCount / $totalBugs) * 100;
                            $analysisTextFunctionality .= "<p><b>{$funcName}:</b> {$bugsCount} bugs, representing " . round($percentageOfTotal, 2) . "% of total.</p>";
                        }

                        if (!empty($urgentFixes)) {
                            $funcNames = implode(', ', $urgentFixes);
                            $recommendationsFunctionalities .= "<p>The functionality {$funcNames} has a significant number of bugs requiring immediate analysis and resolution.</p>";
                        }

                        if (!empty($needsAttention)) {
                            $funcNames = implode(', ', $needsAttention);
                            $recommendationsFunctionalities .= "<p>The functionality {$funcNames} contains a notable number of bugs and requires attention for improvement.</p>";
                        }

                        if (empty($urgentFixes) && empty($needsAttention)) {
                            $recommendationsFunctionalities .= "<p>All functionalities are in satisfactory condition. Continue monitoring and stay vigilant for new bugs.</p>";
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
                        <h5> Defect Density </h5
                            <?php

                            $dev = [];
                            $prod = [];
                            $defectDensityData = [];
                            foreach ($data['defectDensity'] as $i => $item) {
                                $dev[] = $item['dev'];
                                $prod[] = -$item['prod'];
                                $defectDensityData[] = [
                                    'label' => $item['label'],
                                    'data' => [$item['dev'], -$item['prod']],
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

                            $analysisTextDensity = "<h5>Defect Density Analysis by Platform and Environment:</h5>";

                            $devDefectsByPlatform = [];
                            $prodDefectsByPlatform = [];


                            foreach ($defectDensityData as $d) {
                                $platform = htmlspecialchars($d['label'], ENT_QUOTES, 'UTF-8');
                                $prodDefects = $d['data'][1] * -1;
                                $devDefects = $d['data'][0];
                                $totalDefects = $devDefects + $prodDefects;

                                $analysisTextDensity .= "<p>Platform <b>{$platform}</b>:</p>";
                                $analysisTextDensity .= "<ul>";
                                $analysisTextDensity .= "<li>Dev environment: {$devDefects} defects</li>";
                                $analysisTextDensity .= "<li>Prod environment: {$prodDefects} defects</li>";
                                $analysisTextDensity .= "<li>Total defects: " . ($devDefects + $prodDefects) . "</li>";
                                $analysisTextDensity .= "</ul>";
                            }

                            $recommendationsDensity = "<h5>Recommendations:</h5>";

                            $percentDev = 0.80;
                            $percentProd = 0.20;

                            $expectedDevDefects = $totalDefects * $percentDev;
                            $expectedProdDefects = $totalDefects * $percentProd;

                           if ($devDefects > $expectedDevDefects) {
                               $recommendationsDensity .= "<p>Platform <b>{$platform}</b> in dev environment has more defects than expected. It is recommended to conduct a thorough analysis of defect causes and optimize development and testing processes.</p>";
                           }

                           if ($prodDefects > $expectedProdDefects) {
                                $recommendationsDensity .= "<p>Platform <b>{$platform}</b> in prod environment has more defects than expected. Immediate action is required to fix and prevent critical errors.</p>";
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
                    <div class="text m-5"> No data found</div>
                <?php endif; ?>
            </div>
            <div class="tab-pane" id="metrics" role="tabpanel" aria-labelledby="metrics-tab">
                <?php if ($data): ?>
                    <div class="d-flex justify-content-between flex-row align-items-baseline mt-1" style="gap: 20px">
                        <table class="table" style="width: 30%">
                            <thead class="thead-dark">
                            <tr>
                                <th> Sprint</th>
                                <th> Missed bugs</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            foreach ($data['missedBugs'] as $metric) {
                                echo "<tr>";
                                echo "<td class='p-1'>" . $metric['sprint'] . "</td>";
                                echo "<td class='p-1'>" . round($metric['metric'] * 100, 2) . "%</td>";
                                echo "</tr>";

                            }
                            ?>
                            </tbody>
                        </table>

                        <table class="table" style="width: 65%">
                            <thead class="thead-dark">
                            <tr>
                                <th colspan="3" class="text-center"> Average spend time</th>
                                <th colspan="3" class="text-center"> Trend</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr>
                                <?php $status = $data['avgResolveTime']['common']['status'] ?>
                                <td class='text-center'
                                    colspan="3"><?= $data['avgResolveTime']['common']['avgTime'] . ' days' ?></td>
                                <td class='text-center' style="background: <?= $status == 1 ? 'green' : 'red' ?>"
                                    colspan="3"><?= $status == 1 ? 'Good' : 'Bad' ?></td>
                            </tr>
                            </tbody>

                            <thead class="thead-dark">
                            <tr>
                                <th colspan="2"> Functionality</th>
                                <th colspan="2" class="text-center"> Amount</th>
                                <th colspan="2" class="text-center"> Trend</th>
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
                                echo "<td colspan='2'>" . $d['avgTime'] . ' days' . "</td>";
                                echo "<td class='text-center' colspan='2' style='background: " . $status_color . "' colspan='2'>";
                                echo $status == 1 ? 'Good' : 'Bad';
                                echo "</td>";
                                echo "</tr>";

                            }
                            ?>
                            </tbody>
                        </table>

                    </div>

                <?php else: ?>
                    <div class="text m-5"> No data found</div>
                <?php endif; ?>
            </div>
            <div class="tab-pane" id="conclusion" role="tabpanel" aria-labelledby="conclusion-tab">
                <?php if ($data): ?>


                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Analysis of Bugs by Severity:</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisTextSeverity; ?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Analysis of Bugs by Priority:</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisTextPriority;
                                echo "<h5>Recommendations:</h5>";
                                echo "<p>$recommendationsPriority</p>"; ?>
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
                                <?= $analysisTextFunctionality;
                                echo $recommendationsFunctionalities;
                                ?>
                        </div>
                    </div>
                    <div class="card text-bg-info mb-3">
                        <div class="card-header">Defect Density :</div>
                        <div class="card-body">
                            <p class="card-text">
                                <?= $analysisTextDensity;
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
                            $result = preg_replace_callback('/\$(\d+(\.\d+)?)\$/', function ($matches) {
                                $id = $matches[1];
                                $name = Functionality::findOne(['id' => $id])['name'];
                                return "<b>" . $name . "</b>";
                            }, $data['analysisConclusion']);
                            echo $result ?>
                        </div>
                    </div>


                <?php else: ?>
                    <div class="text m-5"> No data found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
