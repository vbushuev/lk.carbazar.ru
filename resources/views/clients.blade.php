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
                                    <li><a href="javascript:{void(0);}" onclick="{$('#adduser').modal();}" class="pull-right" кщду="button"><i class="fa fa-plus"></i>&nbsp; Добавить</a></li>
                                </ul>
                            </div>
                            <!-- /.navbar-collapse -->
                        </div>
                        <!-- /.container-fluid -->
                    </nav>
                    <h4>Пользователи</h4>

                </div>

                <div id="lkcontent" class="panel-body containers-loader" data-name="accounts" data-ref="/data/client/list" data-func="clients"></div>
                <script>
                    window.showupdate=function(r){
                        var str = $(r).text();
                        console.debug(str);
                        var d = JSON.parse(str);
                        $("#updateclient [name=id]").val(d.id);
                        $("#updateclient [name=name]").val(d.name);
                        $("#updateclient [name=login]").val(d.login);
                        $("#updateclient [name=email]").val(d.email);
                        $("#updateclient #title_").text(d.name);
                        $("#updateclient [name=account_id]").val(d.account_id).change();
                        $("#updateclient [name=apikey_id]").val(d.apikey_id);
                        $("#updateclient [name=type]").val(d.type);
                        $("#updateclient").modal();

                    };
                    window.showremove=function(r){
                        var str = $(r).text();
                        console.debug(str);
                        var d = JSON.parse(str);
                        $("#removeclient [name=id]").val(d.id);
                        $("#removeclient #title_, #updateclient #title_2").text(d.name);
                        $("#removeclient").modal();
                    };
                    window.clients = function(d,c){
                        console.debug(d);
                        var s = '<div class="row header">';
                        s+= '<div class="col-md-3">Дата создания</div>';
                        s+= '<div class="col-md-2">Имя</div>';
                        s+= '<div class="col-md-2">Логин</div>';
                        s+= '<div class="col-md-3">Аккаунт</div>';
                        // s+= '<div class="col-md-2">API</div>';
                        s+= '<div class="col-md-2"></div>';
                        s+= '</div>';
                        carbazar["accounts"] = d;
                        for(var i in d){
                            s+='<div class="row item account-item">';
                            s+='<div class="col-md-3 data-date">'+d[i].created_at+'</div>';
                            s+='<div class="col-md-2 data-user">'+d[i].name+'</div>';
                            s+='<div class="col-md-2 data-vin">'+d[i].email+'</div>';
                            s+='<div class="col-md-3 data-vin">'+d[i].account+'</div>';
                            // s+='<div class="col-md-2 data-vin">'+d[i].apikey+'</div>';
                            s+='<div class="col-md-2 data-actions">';
                            s+='<a href="javascript:{void(0);}" onclick="showupdate(\'#client_id_'+d[i].id+'\')"><i class="fa fa-gear"></i>&nbsp; Редактировать</a>';
                            s+='<a href="javascript:{void(0);}" onclick="showremove(\'#client_id_'+d[i].id+'\')"><i class="fa fa-trash"></i>&nbsp; Удалить</a>';
                            s+='</div>';
                            s+='<div class="hide" id="client_id_'+d[i].id+'">'+JSON.stringify(d[i])+'</div>';
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
<div class="modal fade" id="adduser" data-rel="/data/client/add">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="{{Auth::user()->id}}">
            <input type="hidden" name="role" value=""/>
            <div class="modal-header">
                <h5 class="modal-title">Добавление пользователя: <span class="supplier-title" id="title_"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Аккаунт:</span>
                            <select name="account_id" id="account_id_add" class="containers-loader form-control" data-ref="/data/account/list" onchange="carbazar.request('[name=apikey_id]');"  data-func="comboAccounts" aria-describedby="basic-addon2"></select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Имя:</span><input type="text" onkeyup="javascript:{$('#title_').text($(this).val());}" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="name" value=""></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">Логин/Email:</span><input type="text" class="form-control http-link" placeholder="Кол-во запросов" aria-describedby="basic-addon2" name="email" value="100" onkeyup="$('[name=login]').val($(this).val());"></div>
                        <input name="login" type="hidden"/>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">API ключ:</span>
                            <select name="apikey_id" class="containers-loader form-control" data-ref="/data/apikey/list" data-func="comboApikeys" data-filter="#account_id_add" aria-describedby="basic-addon2"></select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Тип пользователя:</span>
                            <select name="type" class="form-control">
                                <option value="user">Обыный пользователь</option>
                                <option value="admin">Администратор филиала</option>
                                <option value="owner">Владелец аккаунта</option>
                                <option value="carsbazar">Администратор API</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Пароль:</span><input type="text" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="password" value=""></div>
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
<div class="modal fade" id="updateclient" data-rel="/data/client/update">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование пользователя: <span class="supplier-title" id="title_"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Аккаунт:</span>
                            <select id="account_id_update" name="account_id" class="containers-loader form-control" data-ref="/data/account/list" onchange="carbazar.request('#apikey_id_update');" data-func="comboAccounts" aria-describedby="basic-addon2"></select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Имя:</span><input type="text" onkeyup="javascript:{$('#title_').text($(this).val());}" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="name" value=""></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">Логин/Email:</span><input type="text" class="form-control http-link" placeholder="Кол-во запросов" aria-describedby="basic-addon2" name="email" value="100" onkeyup="$('[name=login]').val($(this).val());"></div>
                        <input name="login" type="hidden"/>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">API ключ:</span>
                            <select id="apikey_id_update" name="apikey_id" class="containers-loader form-control" data-ref="/data/apikey/list" data-func="comboApikeys" data-filter="#account_id_update" aria-describedby="basic-addon2"></select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Тип пользователя:</span>
                            <select name="type" class="form-control">
                                <option value="user">Обыный пользователь</option>
                                <option value="admin">Администратор филиала</option>
                                <option value="owner">Владелец аккаунта</option>
                                <option value="carsbazar">Администратор API</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Пароль:</span><input type="text" class="form-control" placeholder="Пароль" aria-describedby="basic-addon1" name="password" value=""></div>
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
<div class="modal fade" id="removeclient" data-rel="/data/client/remove">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование пользователя: <span class="supplier-title" id="title_"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        Вы действительно хотите удалить пользователя  <span class="supplier-title" id="title_2"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary submit-data">Удалить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
            </div>
        </div>
        <!-- </form> -->
    </div>
</div><!--end .modal-->

@endsection
