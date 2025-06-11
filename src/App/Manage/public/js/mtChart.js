/* 
 * Object for creating chart
 */
var mtChart = {};
mtChart.isReady = false;
//初始化图表实例
mtChart.init = function (options, dom, types) {
    var that = this;
    // 路径配置
    require.config({
        paths: {
            echarts: '/static/plugins/echarts/build/dist'
        }
    });
    if (!types) {
        types = [
            'echarts',
            'echarts/chart/bar', // 使用柱状（条形）图就加载bar模块，按需加载
            'echarts/chart/line', // 使用折线(面积)图就加载line模块，按需加载
            'echarts/chart/pie', // 使用饼状（圆环）图就加载pie模块，按需加载
            'echarts/chart/funnel', // 使用漏斗图就加载funnel模块，按需加载
        ];
    }
    !dom ? dom = document.getElementById('mt_echarts') : null;
    // 使用
    require(types, function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(dom);
            // 为echarts对象加载数据
            myChart.setOption(options);
            that.isReady == true;
        }
    );
};

//如果以实例化图表，想展示其他数据的图表，可以重新载入数据，避免实例化多个图表对象。
mtChart.reload = function (options, dom) {
    var that = this;
    !dom ? dom = document.getElementById('mt_echarts') : null;
    that.isReady == true ? require('echarts').init(dom).setOption(options) : that.init(options, dom);
};

//将简易的表格参数转换为可供js图表对象读取的参数。
mtChart.transformLineOptions = function (json) {
    /*
     * json需要提供以下键值信息：
     * 必填：title标题;   header展示的对象名称（如曲线图，1个列名对应的是一条曲线）;  x为X横坐标轴;   y为Y纵坐标轴的点参数数组。
     * 可选： subTitle子标题;  type选择供展示的图表类型，第一个为默认显示的类型;   format显示的纵坐标值显示格式;   max是否显示最大值点， min是否显示最小值点  average是否显示平均线
     */
    json.type = json.type ? json.type : ['line'];//'line', 'bar'
    var options = {
        title: {
            text: json.title,
            subtext: json.subTitle ? json.subTitle : ''
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: json.header
        },
        toolbox: {
            show: json.toolbox ? json.toolbox : false,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true, type: json.type},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,
        animation : json.animation ? json.animation : false,
        xAxis: [
            {
                type: 'category',
                boundaryGap: json.boundaryGap ? json.boundaryGap : true, //控制横坐标是点位，还是区间
                data: json.x
            }
        ],
        yAxis: [
            {
                type: 'value',
                axisLabel: {
                    formatter: json.format ? json.format : '{value}'
                }
            }
        ],
        series: []
    };
    if (json.valueFormatter) {
        options.tooltip.formatter = json.valueFormatter;
    }
    var j = 0;
    for (var i in json.header) {
        var yJson = {
            name: json.header[i],
            type: json.type[0],
            data: json.y[j]
        };
        if (json.data_remark) {
            yJson.itemStyle = {
                normal: {
                    label: {
                        show: true,
                        position: 'top',
                        formatter: function (params) {
                            return params.value;
                        },
                        textStyle: {
                            color: 'blue'
                        }
                    }
                }
            };
        }
        var markPoint;
        if (json.max !== false) {
            markPoint = {};
            markPoint.data = [];
        }
        if (json.min !== false) {
            if (!markPoint) {
                markPoint = {};
                markPoint.data = [];
            }
            markPoint.data.push({type: 'min', name: '最小值'});
        }
        var markLine;
        if (json.average !== false) {
            markLine = {
                data: [
                    {type: 'average', name: '平均值'}
                ]
            };
        }
        if (markPoint) {
            yJson.markPoint = markPoint;
        }
        if (markLine) {
            yJson.markLine = markLine;
        }
        options.series.push(yJson);
        j++;
    }
    return options;
};
//将简易的表格参数转换为可供js图表对象读取的参数(柱状图)。
mtChart.transformBarOptions = function (json) {
    json.type = json.type ? json.type : ['bar'];// 'bar','line'

    var options = {
        title: {
            text: json.title,
            subtext: json.subTitle
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: json.header
        },
        toolbox: {
            show: false,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true, type: json.type},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,
        animation : json.animation ? json.animation : false,
        xAxis: [
            {
                type: 'category',
                data: json.x
            }
        ],
        yAxis: [
            {
                type: 'value',
                axisLabel: {
                    formatter: json.format ? json.format : '{value}'
                }
            }
        ],
        series: []
    };
    if (json.valueFormatter) {
        options.tooltip.formatter = json.valueFormatter;
    }
    var j = 0;
    for (var i in json.header) {
        var yJson = {
            name: json.header[i],
            type: json.type[0],
            itemStyle: {
                normal: {
                    /*  color: '#fff',
                     barBorderColor: 'tomato',
                     barBorderWidth: 6,
                     barBorderRadius:0,*/
                    label: {
                        show: true,
                        position: 'top',
                        formatter: function (params) {
                            return params.value;
                        },
                        textStyle: {
                            color: 'blue'
                        }
                    }
                }
            },
            data: json.y[j]
        };
        var markPoint;
        if (json.max !== false) {
            markPoint = {};
            markPoint.data = [];
        }
        if (json.min !== false) {
            if (!markPoint) {
                markPoint = {};
                markPoint.data = [];
            }
            markPoint.data.push({type: 'min', name: '最小值'});
        }
        var markLine;
        if (json.average !== false) {
            markLine = {
                data: [
                    {type: 'average', name: '平均值'}
                ]
            };
        }
        if (markPoint) {
            yJson.markPoint = markPoint;
        }
        if (markLine) {
            yJson.markLine = markLine;
        }
        options.series.push(yJson);
        j++;
    }
    return options;
};
//饼状图参数
mtChart.transformRoundOptions = function (json) {
    json.type = json.type ? json.type : ['pie']; //'pie', 'funnel'
    var options = {
        title: {
            text: json.title,
            subtext: json.subTitle ? json.subTitle : '',
            x: 'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            x: 'left',
            data: []
        },
        toolbox: {
            show: false,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {
                    show: true,
                    type: json.type,
                    option: {
                        funnel: {
                            x: '15%',
                            width: '15%',
                            funnelAlign: 'left',
                            max: 700
                        }
                    }
                },
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,
        animation : json.animation ? json.animation : false,
        series: [
            {
                name: json.header[1],
                type: json.type[0],
                radius: '55%',
                center: ['50%', '60%'],
                data: []
            }
        ]
    };
    if (json.valueFormatter) {
        options.tooltip.formatter = json.valueFormatter;
    }
    for (var i in json.data) {
        options.legend.data.push(json.data[i][0]);
        var sJson = {value: json.data[i][1], name: json.data[i][0]};
        options.series[0].data.push(sJson);
    }
    return options;
};
//横向的柱状图
mtChart.transformHBarOptions = function (json) {
    json.type = json.type ? json.type : ['bar'];// 'bar','line'
    var options = {
        title: {
            text: json.title,
            subtext: json.subTitle
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: json.header
        },
        toolbox: {
            show: false,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true, type: json.type},
                restore: {show: true},
                saveAsImage: {show: true}

            }
        },
        calculable: true,
        animation : json.animation ? json.animation : false,
        xAxis: [
            {
                type: 'value',
                splitLine: {show: false},
                boundaryGap: [0, 0.01]
            }
        ],
        yAxis: [
            {
                type: 'category',
                splitLine: {show: false},
                data: json.x
            }
        ],
        series: []
    };
    for (var i in json.y) {
        options.series.push({name: json.header[i], type: json.type[0], data: json.y[i]});
    }
    return options;
};
//转换为100%堆积图
mtChart.transformStackOptions = function (json) {
    var demoJson = {
        title: '月活跃用户百分比',
        header: ['已登录月活跃用户百分比', '未登录月活跃用户百分比'],
        x: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
        y: [
            [66.66, 50.00, 88.88, 75, 25, 56.50, 17],
            [33.37, 50.00, 11.12, 25, 75, 43.50, 83],
        ]
    };
    if (!json) {
        json = demoJson
    }
    json.type = json.type ? json.type : ['line', 'stack'];  //['line', 'bar', 'stack', 'tiled']
    json.format = json.format ? json.format : '{value}%';
    var options = {
        title: {
            text: json.title,
            subtext: json.subTitle ? json.subTitle : ''
        },
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                var text = '', comma = '<br>', j = 0;
                for (var i in params) {
                    if (j == 0) {
                        text += params[0].name;
                    }
                    text += comma + params[i].seriesName + ':' + json.format.replace('\{value\}', params[i].value);
                    j++;
                }
                return text;
            }
        },
        legend: {
            data: json.header
        },
        toolbox: {
            show: false,
            feature: {
                mark: {show: true},
                dataView: {show: true, readOnly: false},
                magicType: {show: true, type: json.type},
                restore: {show: true},
                saveAsImage: {show: true}
            }
        },
        calculable: true,
        animation : json.animation ? json.animation : false,
        xAxis: [
            {
                type: 'category',
                boundaryGap: false,
                data: json.x
            }
        ],
        yAxis: [
            {
                type: 'value',
                axisLabel: {
                    formatter: json.format
                },
                min: 0,
                max: 100
            }
        ],
        series: []
    };
    if (json.valueFormatter) {
        options.tooltip.formatter = json.valueFormatter;
    }
    var j = 0;
    for (var i in json.header) {
        var yJson = {
            name: json.header[i],
            type: json.type[0],
            stack: '总量',
            itemStyle: {
                normal: {
                    areaStyle: {type: 'default'},
                    label: {
                        show: true,
                        position: 'bottom',
                        formatter: function (params) {
                            return json.format.replace('\{value\}', params.value);
                        },
                        textStyle: {
                            color: 'blue'
                        }
                    }
                }
            },
            data: json.y[j]
        };
        options.series.push(yJson);
        j++;

    }
    return options;
};