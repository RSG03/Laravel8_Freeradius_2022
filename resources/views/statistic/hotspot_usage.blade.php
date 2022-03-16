<style>
    .loader {
        border: 8px solid #f3f3f3; /* Light grey */
        border-top: 8px solid #3498db; /* Blue */
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="content">
    <div id="isi">
        <div class="search-box">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('Location') }}
                                {{ Form::select('id',array('0'=>'All Location') + $router_list,null,array('id'=>'location','class'=>'form-control'))}}
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('From') }}
                                {{ Form::text('date', date('Y-m-01'), ['id'=>'from','class'=>'form-control datetimepicker', 'required']) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('To') }}
                                {{ Form::text('date', date('Y-m-d'), ['id'=>'to','class'=>'form-control datetimepicker', 'required']) }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="loading-report">
        <div class="loader"></div>
    </div>
    <div id="report" style='display:none'>
        <div class="box box-default">
            <div class="box-body">
                <div class="content table-responsive">
                    <div class="container-fluid" id="pcont">
                        <div class="cl-mcont alert" style=""></div>
                        <div class="cl-mcont" style="margin-top:30px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="block-flat">
                                        <div class='box-body table-responsive no-padding'>
                                            <div class="chart-image"></div>
                                            <!--<div id='container' style='width:100%; height:400px;'></div>-->
                                        </div><!-- /.box-body -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD ',
            useCurrent: true
        });
        getData($('#location').val(), $('#from').val(), $('#to').val());
    });

    $('#location').on('change', function(){
        $('#report').fadeOut();
        $('#loading-report').fadeIn();
        getData($('#location').val(), $('#from').val(), $('#to').val());
    });
    $('#from').on('dp.change keydown', function(){
        $('#report').fadeOut();
        $('#loading-report').fadeIn();
        getData($('#location').val(), $('#from').val(), $('#to').val());
    });
    $('#to').on('dp.change keydown', function(){
        $('#report').fadeOut();
        $('#loading-report').fadeIn();
        getData($('#location').val(), $('#from').val(), $('#to').val());
    });

    function getData(location, from, to){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post(
            "{{ admin_base_path('report/stats') }}",
            {
                type:'hotspot-usage',
                location: location,
                from: from,
                to: to
            },
            function(e) {
                if (e['count'] == 0) {
                    $('#loading-report').fadeOut();
                    $('#report').fadeIn();
                    $(".chart-image").html("<div id='container-0' style='width:100%;margin-bottom:100px;'><div class=\"alert alert-warning\">\n" +
                        "  <strong>No data found!</strong></div></div>");
                    return;
                }
                $("div[id^='container-']").remove();
                for (var i = 0; i < e.total_graph; i++) {

                    $(".chart-image").append("<div id='container-"+i+"' style='width:100%;margin-bottom:100px;'></div>");
                    var chartOptions = {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Hotspot Usage'
                        },
                        subtitle: {
                            text: 'Location : ' + e['location'] + ' | Period : ' + e['period'],
                            style: {
                                fontSize: '13px'
                            }
                        },
                        xAxis: {
                            categories: e['tgl'][i],
                            crosshair: true,
                            type: 'category',
                            labels: {
                                style: {
                                    fontSize: '13px',
                                    fontFamily: 'Verdana, sans-serif'
                                }
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Bandwidth (Gb)'
                            }
                        },
                        tooltip: {
                            pointFormat: 'Usages: <b>{point.y} Gb</b>'
                        },
                        series: [
                            {
                                name: 'Upload',
                                data: e['upload'][i],
                                dataLabels: {
                                    x: 10,
                                    y: 12,
                                },
                            }, {
                                name: 'Download',
                                color: '#AA4643',
                                data: e['download'][i],
                                dataLabels: {
                                    x: -10,
                                    y: 12,
                                },
                            }
                        ],
                        exporting: {
                            filename: 'Hotspot Usage ' + e['period'],
                            chartOptions: {
                                chart: {
                                    spacingBottom: 20,
                                    spacingTop: 50,
                                    spacingLeft: 20,
                                    spacingRight: 20,
                                },
                                title: {
                                    style: {
                                        fontSize: '13px'
                                    }
                                },
                                subtitle: {
                                    style: {
                                        fontSize: '6px',
                                        fontFamily: 'Verdana, sans-serif'
                                    }
                                },
                                fontSize: '8px',
                                xAxis:{
                                    labels:{
                                        style:{
                                            fontSize: '8px'
                                        }
                                    },
                                    rotation: 90
                                },
                                yAxis:{
                                    labels:{
                                        style:{
                                            fontSize: '8px'
                                        }
                                    }
                                },
                                labels: {
                                    items: [{style: '6px'}],
                                    style: {
                                        fontSize: '6px'
                                    }
                                },
                                legend: {
                                    style: {
                                        fontSize: '6px'
                                    }
                                }
                            }
                        }
                    };

                    $('#container-'+i).highcharts(chartOptions);
                }

                // $('#container').highcharts(chartOptions);

                $('#loading-report').fadeOut();
                $('#report').fadeIn();
            }
        )
    }

</script>