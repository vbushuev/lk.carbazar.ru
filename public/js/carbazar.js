$.datepicker.regional['ru'] = {
    closeText: 'Закрыть',
    prevText: '&#x3c;Пред',
    nextText: 'След&#x3e;',
    currentText: 'Сегодня',
    monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
    'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
    monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
    'Июл','Авг','Сен','Окт','Ноя','Дек'],
    dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
    dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
    dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
    dateFormat: 'yy-mm-dd',
    firstDay: 1,
    isRTL: false
};
$.datepicker.setDefaults($.datepicker.regional['ru']);
var page = {
    message:function(){
        var def = {title:'Сообщение',body:"",close:function(t){console.debug(t);}},
            args = $.extend(def,arguments.length?arguments[0]:{});
        //$('#message-title').html(args.title);
        $('#message-body').html(args.body);
        $('#message').modal();
        $('#message-close').on('click',function(e){
            args.close(args);
        });
    }
};
var carbazar = {
    vinRequest:function(){
        $.getJSON('/data/vin?vin='+$('[name=vin]').val());
        page.message({body:"Ваш запрос по ВИН номеру "+$('[name=vin]').val()+" поставлен в обрабоку. Результат вы сможете увидеть в списке ниже.",close:function(d){document.location.reload();}});

    },
    getFilters:function(){
        var ret = {};
        $(".filter-field").each(function(){
            var val = $(this).val(),nam = $(this).attr("name"),typ=$(this).attr("data-type");
            if(val.length && nam.length){
                console.debug(nam,val,typ);
                ret[nam]= val;
                if(typeof(typ)!="undefined" && typ=='array'){

                    ret[nam]= val.split(/,/g);
                }
            }
        });
        //console.debug(ret);
        return ret;
    },
    statusSelect:function(s,v){
        var hid = $('[name=status]'),cur = hid.val(),tut = $(s),sta = cur.split(/,/g);
        if(tut.hasClass("selected")){
            tut.removeClass('selected').removeClass('btn-primary').addClass('btn-default');
            sta.splice(sta.indexOf(v), 1);
        }
        else{
            tut.addClass('selected').addClass('btn-primary').removeClass('btn-default');
            sta.push(v);
        }
        hid.val(sta.join(','));
        this.request("#reports");
    },
    reportRequest:function(){
        $.ajax({
            url:"/data",
            data:this.getFilters(),
            dataType:"json",
            success:function(d,x,s){
                console.debug(d);
                var s = '';
                for(var i in d){
                    s+='<div class="row history-item status-'+d[i].status+'">';
                    s+='<div class="col-md-4 data-date">'+d[i].created_at+'</div>';
                    s+='<div class="col-md-2 data-user">'+d[i].user_name+'</div>';
                    s+='<div class="col-md-4 data-vin">'+d[i].vin+'</div>';
                    s+='<div class="col-md-2 data-report">'+((d[i].status=="success")?'<a target="_blank" href="/data/pdf?id='+d[i].id+'">pdf</a>':'<span class="error-message">'+((d[i].message==null)?'запрос обрабатывается...':d[i].message)+'</span>')+'</div>';
                    s+='</div>';
                }
                $("#lkcontent").html(s);
            }
        });
    },
    request:function(){
        var containers = (arguments.length)?arguments[0]:".containers-loader";
        $(containers).each(function(){
            var container = this, $container = $(container),url = $container.attr("data-ref"),name= $container.attr("data-name"),func=$container.attr("data-func"),filter = $container.attr("data-filter");
            console.debug("loading data for "+name);
            filters = carbazar.getFilters();
            if(typeof(filter)!="undefined") {
                var arr = filter.split(/[\s,]/g);
                for(var i=0;i<arr.length;++i){
                    filters[$(arr[i]).attr("name")]= $(arr[i]).val();
                }
            }
            $.ajax({
                url:url,
                data:filters,
                dataType:"json",
                success:function(d,x,s){
                    window[func](d,$container);
                }
            });
        });
    },
    forms:function(){
        var getFormData = function(f){
            var data = {};
            f.find("input,select").each(function(){
                var n=$(this).attr("name"),v=$(this).val();
                if(v.length)data[n]=v;
            });
            return data;
        };
        $(".modal").each(function(){
            var $tut = $(this);

            $(this).find(".submit-data").on("click",function(){
                var url = $tut.attr("data-rel");formData = getFormData($tut);
                console.debug(url,formData);

                $.ajax({
                    url:url,
                    data:formData,
                    dataType:"json",
                    success:function(d,x,s){
                        document.location.reload();
                    }
                });
            });
        });
    },
    csvRequest:function(){
        document.location="/data/csv?"+$.param(this.getFilters());
    },
    account:{
        info:function(){
            var cb = arguments.length?arguments[0]:null;
            $.ajax({
                url:"/data/account",
                dataType:"json",
                success:function(d,x,s){
                    if(typeof(cb)=="function")cb(d[0]);
                }
            });
        }
    },
    apikey:{
        data:{},
        info:function(){
            var cb = arguments.length?arguments[0]:null;
            $.ajax({
                url:"/data/apikey",
                dataType:"json",
                success:function(d,x,s){
                    carbazar.apikey.data = d[0];
                    if(typeof(cb)=="function")cb(d[0]);
                }
            });
        }
    }
};
window.comboAccounts=function(d,c){
    console.debug("comboAccounts ",d);
    c.html('');
    for(var i in d)c.append('<option value="'+d[i].id+'">'+d[i].name+'</option>');
}
window.comboApikeys=function(d,c){
    console.debug("comboApikeys ",d);
    c.html('');
    for(var i in d)c.append('<option value="'+d[i].id+'">'+d[i].apikey+'#'+i+'</option>');
}
$(document).ready(function(){
    //console.clear();
    console.debug("carbazar app loaded");
    $('.type-date').datepicker();
    carbazar.request();
    carbazar.forms();
    // carbazar.apikey.info(function(d){
    //     console.debug(d,$(".apikey-info"));
    //     // $(".apikey-info").html("<sub>Осталось:</sub> "+d.quantity);
    //     $(".apikey-info").html(d.quantity);
    //     $(".apikey").html(d.apikey);
    // });
    setInterval(45000,carbazar.account.info(function(d){
        console.debug(d,$(".account-info"));
        // $(".apikey-info").html("<sub>Осталось:</sub> "+d.quantity);
        $(".apikey-info").html(d.quantity);
    }));
    carbazar.apikey.info(function(d){
        console.debug(d,$(".apikey-info"));
        $(".apikey").html(d.apikey);
    });
    $('body').on("keyup",function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == 13) {
            console.debug('Enter button',$(".enter-button"));
            $(".enter-button").click();
        }
    });
});
