@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- <div class="col-md-10 col-md-offset-1"> -->
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <nav class="navbar navbar-default">
                        <div class="container-fluid">
                            <!-- Brand and toggle get grouped for better mobile display -->
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#filters-navbar-collapse" aria-expanded="false">
                                            <span class="sr-only">Toggle navigation</span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                </button>
                                <a class="navbar-brand" href="#"></a>
                            </div>
                            <!-- Collect the nav links, forms, and other content for toggling -->
                            <!-- <div class="collapse navbar-collapse" id="filters-navbar-collapse">
                                <ul class="nav navbar-nav navbar-left">
                                    <li>
                                        <a href="javascript:{void(0);}"  role="button" aria-haspopup="true" aria-expanded="false">
                                            <div class="btn-group">
                                                <input type="hidden" name="status" class="filter-field" data-type="array" value="success,progress"/>
                                                <button type="button" onclick="carbazar.statusSelect(this,'success');" class="btn btn-primary selected">Успешные</button>
                                                <button type="button" onclick="carbazar.statusSelect(this,'progress');" class="btn btn-primary selected">В процессе</button>
                                                <button type="button" onclick="carbazar.statusSelect(this,'failed');" class="btn btn-default">Не успешные</button>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:{void(0);}" class="" role="button" aria-haspopup="true" aria-expanded="false">
                                            <input class="filter-field type-date" type="text" name="from_date" placeholder="с даты" onchange="carbazar.reportRequest();"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:{void(0);}" class="" role="button" aria-haspopup="true" aria-expanded="false">
                                            <input class="filter-field type-date" type="text" name="to_date" placeholder="по дату" onchange="carbazar.reportRequest();"/>
                                        </a>
                                    </li>
                                </ul>
                            </div> -->
                            <div class="collapse navbar-collapse" id="filters-navbar-collapse">
                                <ul class="nav navbar-nav navbar-right">
                                    <li><a href="javascript:{void(0);}" onclick="$('#addaccount').modal()" class="pull-right" кщду="button"><i class="fa fa-plus"></i>&nbsp; Добавить</a></li>
                                </ul>
                            </div>
                            <!-- /.navbar-collapse -->
                        </div>
                        <!-- /.container-fluid -->
                    </nav>
                    <h4>Аккаунты</h4>

                </div>

                <div id="lkcontent" class="panel-body containers-loader" data-name="accounts" data-ref="/data/account/list" data-func="accounts"></div>
                <script>
                    window.showupdate=function(r){
                        var str = $(r).text();
                        console.debug(str);
                        var d = JSON.parse(str);
                        $("#updateaccount [name=id]").val(d.id);
                        $("#updateaccount [name=name]").val(d.name);
                        $("#updateaccount #title_").val(d.name);
                        $("#updateaccount [name=quantity]").val(d.quantity);
                        $("#updateaccount").modal();

                    };
                    window.accounts = function(d,c){
                        console.debug(d);
                        var s = '<div class="row header">';
                        s+= '<div class="col-md-4">Дата создания</div>';
                        s+= '<div class="col-md-4">Наименование</div>';
                        s+= '<div class="col-md-2">Кол-во запросов</div>';
                        s+= '<div class="col-md-2"></div>';
                        s+= '</div>';
                        carbazar["accounts"] = d;
                        for(var i in d){
                            s+='<div class="row item account-item">';
                            s+='<div class="col-md-4 data-date">'+d[i].created_at+'</div>';
                            s+='<div class="col-md-4 data-user">'+d[i].name+'</div>';
                            s+='<div class="col-md-2 data-vin">'+d[i].quantity+'</div>';
                            s+='<div class="col-md-2 data-vin"><a href="javascript:{void(0);}" onclick="showupdate(\'#account_id_'+d[i].id+'\')"><i class="fa fa-gear"></i>&nbsp; Редактировать</a></div>';
                            s+='<div class="hide" id="account_id_'+d[i].id+'">'+JSON.stringify(d[i])+'</div>';
                            // s+='<div class="col-md-2 data-report">'+((d[i].status=="success")?'<a target="_blank" href="/data/pdf?id='+d[i].id+'">pdf</a>':'<span class="error-message">'+((d[i].message==null)?'запрос обрабатывается...':d[i].message)+'</span>')+'</div>';
                            s+='</div>';
                        }
                        c.html(s);
                    }
                </script>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="message" >
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="message-title"></h5>
                <button type="button" class="close" id="message-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                <div class="col-md-10  col-md-offset-1" id="message-body"></div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="addaccount" data-rel="/data/account/add">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="{{Auth::user()->id}}">
            <input type="hidden" name="role" value=""/>
            <div class="modal-header">
                <h5 class="modal-title">Добавление аккаунта: <span class="supplier-title" id="title_"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Наименование:</span><input type="text" onkeyup="javascript:{$('#title_').text($(this).val());}" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="name" value=""></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">Кол-во запросов:</span><input type="text" class="form-control http-link" placeholder="Кол-во запросов" aria-describedby="basic-addon2" name="quantity" value="100"></div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary submit-data">Сохранить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
            </div>
        </div>
        <!-- </form> -->
    </div>
</div><!--end .modal-->
<div class="modal fade" id="updateaccount" data-rel="/data/account/update">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="role" value=""/>
            <div class="modal-header">
                <h5 class="modal-title">Редактирование аккаунта: <span class="supplier-title" id="title_"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Наименование:</span><input type="text" onkeyup="javascript:{$('#title_').text($(this).val());}" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="name" value=""></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">Кол-во запросов:</span><input type="text" class="form-control http-link" placeholder="Кол-во запросов" aria-describedby="basic-addon2" name="quantity" value="100"></div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary submit-data">Сохранить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
            </div>
        </div>
        <!-- </form> -->
    </div>
</div><!--end .modal-->

@endsection
