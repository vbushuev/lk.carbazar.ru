@extends('layouts.app')

@section('content')
<div class="modal fade" id="user_update" data-rel="/data/user/update">
    <div class="modal-dialog modal-lg">
        <!-- <form action="/data/user/update" type="GET"> -->
        <div class="modal-content">
            <input type="hidden" name="id" value="{{Auth::user()->id}}">
            <input type="hidden" name="role" value=""/>
            <div class="modal-header">
                <h5 class="modal-title">Пользователь: <span class="supplier-title" id="title_">{{$user->name}}</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 "></div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">API ключ:</span><span class="apikey disabled"></span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon1">Имя:</span><input type="text" onkeyup="javascript:{$('#title_').text($(this).val());}" class="form-control" placeholder="Наименование" aria-describedby="basic-addon1" name="name" value="{{$user->name}}"></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">E-mail:</span><input type="text" class="form-control http-link" placeholder="Ссылка" aria-describedby="basic-addon2" name="email" value="{{$user->email}}"></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon2">API логин:</span><input type="text" class="form-control http-link" placeholder="Ссылка" aria-describedby="basic-addon2" name="email" value="{{$user->login}}"></div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 ">
                        <a href="#" onclick="{var sp=$('.set-password');if(sp.hasClass('hide'))sp.removeClass('hide');else sp.addClass('hide');}">Установить новый пароль</a>
                    </div>

                </div>
                <div class="row hide set-password">

                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon4">Новый пароль:</span><input type="password" class="form-control" placeholder="пароль" aria-describedby="basic-addon4" name="password" value=""></div>
                    </div>
                    <div class="col-md-10 col-md-offset-1">
                        <div class="input-group"><span class="input-group-addon" id="basic-addon4">Повторите:</span><input type="password" class="form-control" placeholder="пароль" aria-describedby="basic-addon4" name="password_check" value=""></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
            </div>
        </div>
        <!-- </form> -->
    </div>
</div><!--end .modal-->
<div class="container">
    <div class="row">
        <div class="col-md-10 ">
            <form class="navbar-form navbar-left">
                <div class="form-group">
                    VIN:
                    <input type="text" class="form-control search" placeholder="поиск" onkeyup="#" name="vin" style="width:20rem;">
                    <a class="button enter-button" href="#" onclick="carbazar.vinRequest();">Запросить</a>
                </div>
            </form>
        </div>
        <div class="col-md-2">
            <form class="navbar-form navbar-left">
                <div class="form-group">
                    <a href="javascript:javascript:carbazar.csvRequest();" class="" role="button" aria-haspopup="true" aria-expanded="false">
                        CSV экспорт
                    </a>
                </div>
            </form>
        </div>
    </div>
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
                            <div class="collapse navbar-collapse" id="filters-navbar-collapse">
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
                            </div>
                            <div class="collapse navbar-collapse" id="filters-navbar-collapse">
                                <ul class="nav navbar-nav navbar-right">

                                    <li>

                                    </li>
                                </ul>
                            </div>
                            <!-- /.navbar-collapse -->
                        </div>
                        <!-- /.container-fluid -->
                    </nav>
                    <h4>История запросов</h4>
                </div>

                <div id="lkcontent" class="panel-body">
                </div>
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
@endsection
