var mtHighCharts={};
//线性图参数。
mtHighCharts.transformLineOptions=function(json){
    json.type=json.type?json.type:['line'];
    json.unit=json.unit?json.unit:"";
    json.yMin=json.yMin?json.yMin:0;
    json.step = Math.ceil(json.x.length/15);
    var options={ 
        chart: { 
            type: json.type[0],
            backgroundColor: 'rgba(0,0,0,0)'
        }, 
        title: { 
            text: json.title 
        }, 
        subtitle: { 
            text: json.subTitle?json.subTitle:'' 
        }, 
        xAxis: { 
            categories: json.x,
            labels:{
                step:json.step, //控制间隔显示横坐标
                staggerLines: 1
            }
        }, 
        yAxis: { 
            min: json.yMin,
            title: { 
                text: json.yText?json.yText:'' 
            } 
        }, 
        tooltip: {
            shared: true,
            crosshairs: true,
            enabled: true,
            formatter: function() {
                if (this.points instanceof Array) {
                    this.points.sort(function(a,b){
                        return b.y - a.y;
                    });
                    var html = this.x + '<br>';
                    this.points.forEach(function(value,i){
                        html += value.series.name + '：' + value.y + json.unit + '<br>';
                    })
                    return html;
                } else {
                    return '<b>'+ this.series.name +'</b><br>'+this.x +': '+ this.y +json.unit;
                }

            }
        }, 
        plotOptions: { 
            line: { 
                connectNulls:true,
                dataLabels: {
                    enabled: json.label ? json.label : false //坐标点是是否显示数据
                }, 
                enableMouseTracking: true
            }
        }, 
        series: [] 
    };
    for(var i in json.y){
        options.series.push( {
            name:json.header[i],
            data:json.y[i]
            } );
    }
    return options;   
};
//柱状图参数。
mtHighCharts.transformBarOptions=function(json){
    json.type=json.type?json.type:['column'];
    json.unit=json.unit?json.unit:"";
    json.floatval=json.floatval?json.floatval:1;
    var options={
        chart: {
            type: json.type[0],
            backgroundColor: 'rgba(0,0,0,0)'
        },
        title: {
            text: json.title
        },
        subtitle: {
            text:  json.subTitle?json.subTitle:'' 
        },
        xAxis: {
            categories: json.x
        },
        yAxis: {
            min: 0,
            title: {
                text: json.yText?json.yText:''
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.'+json.floatval+'f} '+json.unit+'</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                dataLabels: {
                    enabled: true
                }
            },
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: []
    };
    for(var i in json.y){
        options.series.push( {
            name:json.header[i],
            data:json.y[i]
            } );
    }
    return options;    
};
//饼状图参数。
mtHighCharts.transformRoundOptions=function(json){
    json.type=json.type?json.type:['pie'];
    json.floatval=json.floatval?json.floatval:1;
    var options={
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: json.title
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.'+json.floatval+'f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.'+json.floatval+'f} %'
                }
            }
        },
        series: [{
            type: json.type[0],
            name: json.subTitle?json.subTitle:'',
            data: json.data //[['Firefox',  111],{name: 'Chrome',y: 222, sliced: true,selected: true }, ['Safari',    333]]
        }]
    };				
    return options;
};