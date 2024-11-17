@extends('layouts/layoutMaster')
@section('title', 'Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.6.0/css/flag-icon.min.css">
<style>
    .dashboard-top-card {
        background: rgb(8, 13, 111);
        background: linear-gradient(180deg, rgba(8, 13, 111, 1) 34%, rgba(121, 26, 9, 1) 100%, rgba(0, 212, 255, 1) 100%);
    }

    .circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        /* Adjust width as needed */
        height: 40px;
        /* Adjust height as needed */
        border-radius: 50%;
        /* Makes it a circle */
        font-size: 1rem;
        /* Adjust font size as needed */
    }

    .today-count {}
</style>

@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>

<script type="text/javascript">
    // Monthly Online Order History
    var monthly_online_pie_basic_element = document.getElementById('monthly_online_pie_basic');
    if (monthly_online_pie_basic_element) {
        var monthly_online_pie_basic = echarts.init(monthly_online_pie_basic_element);
        monthly_online_pie_basic.setOption({
            color: [
                '#2ec7c9','#b6a2de','#5ab1ef','#0fe12b','#d87a80',
                '#8d98b3','#e5cf0d','#97b552','#95706d','#dc69aa',
                '#07a2a4','#9a7fd1','#588dd5','#f5994e','#c05050',
                '#59678c','#c9ab00','#7eb00a','#6f5553','#c14089'
            ],

            textStyle: {
                fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                fontSize: 13
            },

            title: {
                text: 'Monthly Online Order Status',
                left: 'center',
                textStyle: {
                    fontSize: 17,
                    fontWeight: 500
                },
                subtextStyle: {
                    fontSize: 12
                }
            },

            tooltip: {
                trigger: 'item',
                backgroundColor: 'rgba(0,0,0,0.75)',
                padding: [10, 15],
                textStyle: {
                    fontSize: 13,
                    fontFamily: 'Roboto, sans-serif'
                },
                formatter: "{a} <br/>{b}: {c} ({d}%)"
            },

            legend: {
                orient: 'horizontal',
                bottom: '0%',
                left: 'center',
                data: ['Pending','Packaging','Shipped','Delivered','Cancel','Return'],
                itemHeight: 8,
                itemWidth: 8
            },

            series: [{
                name: 'Product Type',
                type: 'pie',
                radius: '70%',
                center: ['50%', '50%'],
                itemStyle: {
                    normal: {
                        borderWidth: 1,
                        borderColor: '#fff'
                    }
                },
                data: [
                    {value: {{$monthly_online_order['1'] ?? 0 ? $monthly_online_order['1']->quantity : 0 }}, name: 'Pending'},
                    {value: {{$monthly_online_order['2'] ?? 0 ? $monthly_online_order['2']->quantity : 0 }}, name: 'Packaging'},
                    {value: {{$monthly_online_order['3'] ?? 0 ? $monthly_online_order['3']->quantity : 0 }}, name: 'Shipped'},
                    {value: {{$monthly_online_order['5'] ?? 0 ? $monthly_online_order['5']->quantity : 0 }}, name: 'Cancel'},
                    {value: {{$monthly_online_order['4'] ?? 0 ? $monthly_online_order['4']->quantity : 0 }}, name: 'Delivered'},
                    {value: {{$monthly_online_order['6'] ?? 0 ? $monthly_online_order['6']->quantity : 0 }}, name: 'Return'}
                ]
            }]
        });
    }
</script>

@endsection

@section('content')
<div class="row-background">
    <div class="card bg-info">
        <div class="row">
            <div class="col-sm-4">
                <div class="card-body">
                    <h2 class="card-text"><span class="text-danger"><i class="fas fa-chart-line"></span></i>
                        Admin Dashboard</h2>
                </div>

            </div>
            <div class="col-sm-8">
                <div class="card-body float-right">
                    <i class="flag-icon flag-icon-us"></i>
                    <h2 class="card-text text-right">Welcome : {{ $user->name }} ( {{ $user->email }}) </h2>
                </div>

            </div>
        </div>
    </div>
    <div class="card dashboard-top-card mb-2 mt-2">
        <div class="row">
            <div class="col-sm-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="card-text text-white"><i class="fas fa-angle-double-right"></i> Important </h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 d-flex align-items-center">
                <div class="card-body">
                    <a href="">
                        <p class="card-text text-white text-right"><i class="fas fa-circle"></i> Pending Deposit
                        </p>
                    </a>
                </div>
            </div>
            <div class="col-sm-3 d-flex align-items-center">
                <div class="card-body">
                    <a href="">
                        <p class="card-text text-white text-right"><i class="fas fa-circle"></i> Withdraw Request </p>
                    </a>
                </div>
            </div>
            <div class="col-sm-3 d-flex align-items-center">
                <div class="card-body">
                    <a href="">
                        <p class="card-text text-white text-right"><i class="fas fa-circle"></i> Support Ticket </p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-info text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white"> {{ $activesalesData->today_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Today Sell <span class="circle bg-danger">{{
                                    $activesalesData->today_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-success text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white"> {{ $activesalesData->month_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Monthly Sell <span class="circle bg-danger">{{
                                    $activesalesData->month_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-primary text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white">{{
                                $activesalesData->pending_today_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Today Pending <span class="circle bg-danger">{{
                                    $activesalesData->pending_today_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-danger text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white">{{
                                $activesalesData->pending_month_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Monthly Pending <span class="circle bg-success">{{
                                    $activesalesData->pending_month_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-primary text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white"> {{
                                $activesalesData->packaging_month_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Today Packaging <span class="circle bg-danger">{{
                                    $activesalesData->packaging_month_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-warning text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white"> {{ $activesalesData->month_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Monthly Packaging <span class="circle bg-danger">{{
                                    $activesalesData->month_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-success text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white">{{
                                $activesalesData->pending_today_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Today Pending <span class="circle bg-danger">{{
                                    $activesalesData->pending_today_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body bg-info text-white p-4 rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex justify-content-center">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <div class="col-md-9 text-center text-md-start">
                            <h1 class="card-text mb-1 font-weight-bold text-white">{{
                                $activesalesData->pending_month_total }}
                                TK</h1>
                            <h5 class="mb-0 text-white">Monthly Pending <span class="circle bg-danger">{{
                                    $activesalesData->pending_month_count }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>

<div class="row">
    <div class="col-xl-12" style="margin-top: 30px;">
        <div class="card">
            <div class="card-body">
                <div class="chart-container row">
                    <div class="col-md-6 monthly-report">
                        <h3 class="mb-10">Monthly Online Order History</h3>
                        @if(isset($monthly_online_order))
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Amount</th>
                                    <th> % 100</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total_number=$monthly_online_order->sum('quantity');
                                $total_amount=$monthly_online_order->sum('total');
                                @endphp
                                @foreach((json_decode($monthly_online_order,true)) as $key=>$order)
                                <tr>
                                    <td>{{$status[$key]->name}}</td>
                                    <td>{{$order['quantity']}}</td>
                                    <td>{{$order['total']}}</td>
                                    <td>
                                        {{number_format(($order['quantity']/$total_number)*100,2)}} %
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tbody>
                                <tr class="bg-info">
                                    <td>Total</td>
                                    <td>{{$total_number}}</td>
                                    <td>{{$total_amount}}</td>
                                    <td>100 % </td>
                            </tbody>
                        </table>
                        @endif
                    </div>
                    <div class="chart has-fixed-height col-md-6" id="monthly_online_pie_basic">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection