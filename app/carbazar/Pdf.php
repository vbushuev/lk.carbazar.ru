<?php
namespace App\carbazar;
class Pdf extends FPDF{
    protected $vin="";
    protected $domain = null;
    public function setVin($v){
        $this->vin = $v;
    }
    public function Report($report,$domain=null){
        $this->domain = $domain;
        $this->vin = $report["history"]["vin"];
        $pdf = $this;
        $pdf->AddPage();
        $pdf->HeadingBlue('ОБЩИЕ СВЕДЕНИЯ ОБ АВТОМОБИЛЕ');
        $odd = false;
        if(isset($report['history']['RequestResult']['vehicle']))$pdf->Row('Модель:',$report['history']['RequestResult']['vehicle']['model'],$odd);
        $pdf->Row('VIN:',$this->vin,$odd);
        if(isset($report['history']['RequestResult']['vehicle']['year']))$pdf->Row('Год производства:',$report['history']['RequestResult']['vehicle']['year'],$odd);
        if(isset($report['history']['RequestResult']['vehicle']['color']))$pdf->Row('Цвет:',$report['history']['RequestResult']['vehicle']['color'],$odd);
        if(isset($report['history']['RequestResult']['vehicle']['powerHp']))$pdf->Row('Мощность:',$report['history']['RequestResult']['vehicle']['powerHp'],$odd);
        if(isset($report['history']['RequestResult']['vehicle']['engineVolume']))$pdf->Row('Объем двигателя:',$report['history']['RequestResult']['vehicle']['engineVolume'],$odd);
        if(isset($report['history']['RequestResult']['vehicle']))$pdf->Row('Тип:',$this->getCategory($report['history']['RequestResult']['vehicle']['category']),$odd);

        $pdf->HeadingBlue('ДАННЫЕ ТАМОЖНИ');$odd = false;
        if(isset($report['history']['RequestResult']['vehicle']))$pdf->Row("Дата декларации",$report["history"]["RequestResult"]["vehicle"]["year"],$odd);
        $pdf->Row("Страна вывоз",$this->getCountry($this->vin),$odd);

        $pdf->HeadingBlue('ДАННЫЕ ОСАГО');$odd = false;
        $osagoPrice = '';
        if(isset($report["rca"]["errorMessage"]) && !empty($report["rca"]["errorMessage"])){
            $pdf->RowHint($report["rca"]["errorMessage"],$odd);
        }
        else if (is_array($report["rca"]["policyResponseUIItems"])){
            $pdf->Row("Серия договора",$report["rca"]["policyResponseUIItems"][0]["policyBsoSerial"],$odd);
            $pdf->Row("Номер договора",$report["rca"]["policyResponseUIItems"][0]["policyBsoNumber"],$odd);
            $pdf->Row("Ограничения лиц",($report["rca"]["policyResponseUIItems"][0]["policyIsRestrict"]=="1")?"С ограничениями":"Без ограничений",$odd);
            $pdf->Row("Страховая компания",$report["rca"]["policyResponseUIItems"][0]["insCompanyName"],$odd);
            if(is_null($this->domain) || $this->domain!="lk.profinline-vin.ru") $pdf->Row("Примерная стоимость ОСАГО на 1 год",$osagoPrice,$odd,["href"=>"http://cars-bazar.ru/uslugi/osago/","text"=>"Купить ОСАГО"]);
        }

        $pdf->HeadingGreen('РЫНОЧНАЯ СТОИМОСТЬ');$odd = false;
        $carpriceval =(isset($report["carprice"]) && $report["carprice"]!=null && isset($report["carprice"]["car_price_from"]))
            ?"от ".$report["carprice"]["car_price_from"]." до ".$report["carprice"]["car_price"]
            :"Не определено";
        $pdf->Row("Примерная стоимость",$carpriceval,$odd);

        $pdf->HeadingBlue('ПРОБЕГ АВТОМОБИЛЯ');$odd = false;
        $pdf->Row("Значение","Данные не зафиксированы",$odd);

        $pdf->HeadingBlue('СВЕДЕНИЯ ОБ УЧАСТИИ В ДТП');$odd = false;
        if(isset($report["dtp"]["RequestResult"]) && count($report["dtp"]["RequestResult"]["Accidents"])){
            foreach($report["dtp"]["RequestResult"]["Accidents"] as $acident){
                $pdf->Row("Информация о ДТП №".$acident["AccidentNumber"],$odd);
                $pdf->Row("Тип ДТП",$acident["AccidentType"],$odd);
                $pdf->Row("Место ДТП",$acident["RegionName"],$odd);
                $pdf->Row("Время ДТП",$acident["AccidentDateTime"],$odd);
                $pdf->Row("Марка (модель) ТС",$acident["VehicleMark"]." (".$acident["VehicleModel"].")",$odd);
                $pdf->Row("Статус",$acident["VehicleDamageState"]." ".join($acident["DamagePoints"],', '),$odd);
                $pdf->Row("Год выпуска ТС",$acident["VehicleYear"],$odd);
            }
        }
        else {
            $pdf->RowLine("Данные не зафиксированы",$odd);
            $pdf->RowHint("Мы не смогли найти факты, которые указывают на наличие ДТП. Тем не менее, это не означает, что данный автомобиль НЕ УЧАСТВОВАЛ в ДТП",$odd);
        }

        $pdf->HeadingBlue('КОЛИЧЕСТВО ВЛАДЕЛЬЦЕВ');$odd = false;
        if(isset($report['history']['RequestResult']['ownershipPeriods']))$pdf->Row("Владельцев",count($report["history"]["RequestResult"]["ownershipPeriods"]["ownershipPeriod"]),$odd);

        $pdf->HeadingGreen('ИСТОРИЯ РЕГИСТРАЦИОННЫХ ДЕЙСТВИЙ');$odd = false;
        if(isset($report['history']['RequestResult']['ownershipPeriods'])){
            foreach($report["history"]["RequestResult"]["ownershipPeriods"]["ownershipPeriod"] as $owner){
                $to  = isset($owner["to"])?$owner["to"]:"По текущий момент";
                $pdf->Row("Дата последней операции",$to,$odd);
            }
        }

        $pdf->HeadingBlue('ИНФОРМАЦИЯ О РОЗЫСКЕ ТС, В СИСТЕМЕ МВД РОССИИ');$odd = false;
        $wanted = "ГИБДД подтвердило, что автомобиль не числится в угоне";
        if(isset($report["wanted"]["RequestResult"])&&$report["wanted"]["RequestResult"]["count"]>0){
            $wanted = $report["wanted"]["RequestResult"];
        }
        $pdf->RowLine($wanted,$odd);

        $pdf->HeadingBlue('ИНФОРМАЦИЯ О НАЛОЖЕНИИ ОГРАНИЧЕНИЙ В ГОСАВТОИНСПЕКЦИИ НА ТС');$odd = false;
        if(isset($report["restrict"])&&isset($report["restrict"]["RequestResult"])&&isset($report["restrict"]["RequestResult"]["count"])&&$report["restrict"]["RequestResult"]["count"]>0){
            //$wanted = join($report["restrict"]["RequestResult"],", ");
            $organs =[
                "не предусмотренный код",
                "Судебные органы",
                "Судебный пристав",
                "Таможенные органы",
                "Органы социальной защиты",
                "Нотариус",
                "ОВД или иные правоохр. органы",
                "ОВД или иные правоохр. органы (прочие)"
            ];
            $ogr = [
                "",
                "Запрет на регистрационные действия",
                "Запрет на снятие с учета",
                "Запрет на регистрационные действия и прохождение ГТО",
                "Утилизация (для транспорта не старше 5 лет)",
                "Аннулирование"
            ];
            $restrictedItems = $report["restrict"]["RequestResult"]["records"];
            foreach ($restrictedItems as $restrict) {
                // var osnOgr = getText($restrict["osnOgr"], 'н.д.');
                // osnOgr = setLinkToIp(osnOgr);
                //$pdf->Row("Дата последней операции",$to,$odd);
                // $pdf->Row("Информация об ограничении ",$restrict["gid"],$odd);
                $pdf->HeadingOrange("Информация об ограничении ".$restrict["gid"]);$odd = false;
                $pdf->Row("Марка (модель) ТС",$restrict["tsmodel"],$odd);
                $pdf->Row("Год выпуска ТС",($restrict["tsyear"] === null)?'-':$restrict["tsyear"].' г.',$odd);
                $pdf->Row("Дата наложения ограничения",$restrict["dateogr"],$odd);
                $pdf->Row("Регион инициатора ограничения",$restrict["regname"],$odd);
                $pdf->Row("Кем наложено ограничение",$organs[$restrict["divtype"]],$odd);
                $pdf->Row("Вид ограничения",$ogr[$restrict["ogrkod"]],$odd);
                $pdf->Row("Основания ограничения",$restrict["osnOgr"],$odd);
            }
        }
        else $pdf->RowLine("Ограничений нет (проверено в ГИБДД)",$odd);

        $pdf->HeadingBlue('УТИЛИЗАЦИЯ');$odd = false;
        $pdf->RowLine('Автомобиль не был утилизирован (Проверено в ГИБДД)',$odd);

        $pdf->HeadingBlue('ИСПОЛЬЗОВАНИЕ АВТОМОБИЛЯ В КАЧЕСТВЕ ТАКСИ');$odd = false;
        $pdf->RowLine('На автомобиль не выдавалась лицензия на такси',$odd);

        $pdf->HeadingOrange('ИНФОРМАЦИЯ О НАХОЖДЕНИИ В ЗАЛОГЕ У БАНКОВ');$odd = false;
        $zal = 'Данный автомобиль не числится в залоге';
        if($report["zalog"]!=null){
            if(isset($report["zalog"]["list"])){
                $zal = [];
                foreach($report["zalog"]["list"] as $zl){
                    $props = [];
                    $debitors =[];
                    $creditors=[];
                    foreach($zl["properties"] as $properties)$props[]=$properties["prefix"].": "+$properties["value"];
                    foreach($zl["pledgors"] as $pledgors)$debitors[]=($pledgors["type"]=="person")?$pledgors["name"]+" ("+$pledgors["birth"]+")":$pledgors["name"];
                    foreach($zl["pledgees"] as $pledgees)$creditors[]=($pledgees["type"]=="person")?$pledgees["name"]+" ("+$pledgees["birth"]+")":$pledgees["name"];

                    $pdf->Row("Дата регистрации",$zlregisterDate,$odd);
                    $pdf->Row("Номер уведомления",$zl["referenceNumber"],$odd);
                    $pdf->Row("Имущество",join($props,', '),$odd);
                    $pdf->Row("Залогодержатель",join($debitors,', '),$odd);
                    $pdf->Row("Залогодатель",join($creditors,', '),$odd);
                }
            }
            else $zal = 'Данный автомобиль не числится в залоге';
        }
        if(!is_array($zal))$pdf->RowLine($zal,$odd);
        return $pdf->Output('S',$this->vin.".pdf",true);

    }
    public function FastReport($report){

        $this->vin = $report["history"]["vin"];
        $pdf = $this;
        $pdf->AddPage();
        $pdf->HeadingBlue('ОБЩИЕ СВЕДЕНИЯ ОБ АВТОМОБИЛЕ');
        $odd = false;
        if(isset($report['history']['RequestResult']['vehicle']['model']))$pdf->Row('Модель:',$report['history']['RequestResult']['vehicle']['model'],$odd);
        if(isset($report["history"]["vin"]))$pdf->Row('VIN:',$report["history"]["vin"],$odd);
        if(isset($report['history']['RequestResult']['vehicle']['year']))$pdf->Row('Год производства:',$report['history']['RequestResult']['vehicle']['year'],$odd);
        $pdf->Row('Цвет:','Доступно в полном отчете',$odd);
        if(isset($report['history']['RequestResult']['vehicle']['powerHp']))$pdf->Row('Мощность:',$report['history']['RequestResult']['vehicle']['powerHp'],$odd);
        if(isset($report['history']['RequestResult']['vehicle']['engineVolume']))$pdf->Row('Объем двигателя:',$report['history']['RequestResult']['vehicle']['engineVolume'],$odd);
        $pdf->Row('Тип:','Доступно в полном отчете',$odd);
        $pdf->Row("Данные таможни",'Доступно в полном отчете',$odd);
        $pdf->Row("Данные осаго",'Доступно в полном отчете',$odd);
        $pdf->Row("Рыночная стоимость",'Доступно в полном отчете',$odd);
        $pdf->Row("Пробег автомобиля",'Доступно в полном отчете',$odd);
        $pdf->Row("Сведения об участии в дтп",'Доступно в полном отчете',$odd);
        $pdf->Row("Количество владельцев",'Доступно в полном отчете',$odd);
        $pdf->Row("История регистрационных действий",'Доступно в полном отчете',$odd);
        $pdf->Row("Информация о розыске ТС",'Доступно в полном отчете',$odd);
        $pdf->Row("Информация ою ограничениях",'Доступно в полном отчете',$odd);
        $pdf->Row("Утилизация",'Доступно в полном отчете',$odd);
        //$pdf->Row("Использование автомобиля в качестве такси",'Доступно в полном отчете',$odd);
        $pdf->Row('Информация о залоге','Доступно в полном отчете',$odd);
        $pdf->Row("","",$odd,["href"=>"http://checkauto.cars-bazar.ru/report.html?vin=".$this->vin,"text"=>"Полный отчет"]);
        if(!is_array($zal))$pdf->RowLine($zal,$odd);
        return $pdf->Output('S',$rq["vin"].".pdf",true);
    }
    function Header(){
        // Logo
        if(!is_null($this->domain) && $this->domain=="lk.profinline-vin.ru")$this->Image('http://lk.profinline-vin.ru/public/img/autolombard.png',10,10,40,0,'','http://franclombard.ru/');
        else  $this->Image('http://checkauto.cars-bazar.ru/img/site_logo_dark.png',10,10,40,0,'','http://checkauto.cars-bazar.ru');
        // Arial bold 15
        //define('FPDF_FONTPATH',"fonts/");
        $this->AddFont('MyriadProRegular','','MyriadProRegular.php');
        $this->AddFont('MyriadProItalic','','MyriadProItalic.php');
        $this->AddFont('MyriadProBoldItalic','','MyriadProBoldItalic.php');
        $this->AddFont('MyriadProBold','','MyriadProBold.php');
        //$this->AddFont('myriad','B','MyriadProBold/MyriadProBold.ttf',true);
        $this->SetFont('MyriadProBoldItalic','',24);
        // Move to the right
        $this->Cell(50);
        // Title
        $this->Cell(30,10,iconv('utf-8','cp1251','Полный отчет по'),0,0,'L');
        $this->Ln(10);
        $this->Cell(50);
        $this->Cell(16,10,iconv('utf-8','cp1251','VIN '),0,0,'L');
        $this->SetFont('MyriadProRegular','',26);
        $this->Cell(30,10,$this->vin,0,0,'L');
        // Line break
        $this->Ln(20);
    }
    // Page footer
    function Footer(){
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('MyriadProRegular','',8);
        // Page number
        //$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
    public function RowLine($h,&$odd=false){
        $this->SetFillColor(240, 242, 246);
        $this->SetFont('MyriadProBold','',12);
        $this->SetTextColor(0,0,0);
        $this->Cell(0,14,iconv('utf-8','cp1251',$h),0,0,'C',$odd);
        $this->Ln(14);
        $odd=!$odd;
    }
    public function RowHint($h,&$odd=false){
        $this->SetFillColor(240, 242, 246);
        $this->SetFont('MyriadProItalic','',10);
        $this->SetTextColor(0,0,0);
        if(strlen($h)>80){//need wrap
            $h = iconv('utf-8','cp1251',$h);
            $vv = substr($h,0,80);
            $vv = strrev($vv);
            $pos = 80-strpos(" ",$vv);
            $this->Cell(0,7,substr($h,0,$pos-1),0,0,'C',$odd);
            $this->Ln(7);
            $this->Cell(0,7,substr($h,$pos-1),0,0,'C',$odd);
        }
        else $this->Cell(0,14,iconv('utf-8','cp1251',$h),0,0,'C',$odd);
        $this->Ln(14);
        $odd=!$odd;
    }
    public function Row($h,$v,&$odd=false,$link=false){
        $rowWidth = 50;
        $this->SetFillColor(240, 242, 246);
        $this->SetFont('MyriadProRegular','',12);
        $this->SetTextColor(10,10,10);
        $this->Cell(88,14,iconv('utf-8','cp1251',$h),0,0,'L',$odd);
        $this->SetFont('MyriadProBold','',12);
        $this->SetTextColor(0,0,0);
        if(strlen($v)>($rowWidth*2)){//need wrap
            $v = iconv('utf-8','cp1251',$v);
            $vv = substr($v,0,$rowWidth);
            $vv = strrev($vv);
            $pos = $rowWidth-strpos(" ",$vv);
            $this->Cell(0,7,substr($v,0,$pos-1),0,0,'L',$odd);
            $this->Ln(7);
            $this->Cell(88);
            $this->Cell(0,7,substr($v,$pos-1),0,0,'L',$odd);
        }
        else if(strlen($v)>$rowWidth){//need wrap
            $v = iconv('utf-8','cp1251',$v);
            $vv = substr($v,0,$rowWidth);
            $vv = strrev($vv);
            $pos = $rowWidth-strpos(" ",$vv);
            $this->Cell(0,7,substr($v,0,$pos-1),0,0,'L',$odd);
            $this->Ln(7);
            $this->Cell(88);
            $this->Cell(0,7,substr($v,$pos-1),0,0,'L',$odd);
        }
        else if($link!==false){
            $this->Cell(40,14,iconv('utf-8','cp1251',$v),0,0,'L',$odd);
            $this->SetFillColor(236, 210, 35);
            $this->Cell(40,14,iconv('utf-8','cp1251',$link["text"]),0,0,'C',true,$link["href"]);
        }else $this->Cell(0,14,iconv('utf-8','cp1251',$v),0,0,'L',$odd);
        $this->Ln(14);
        $odd=!$odd;

    }
    protected function wrap($v){
        $rowWidth = 50;
        if(strlen($v)>$rowWidth){//need wrap
            $vv = substr($v,0,$rowWidth);
            $vv = strrev($vv);
            $pos = $rowWidth-strpos(" ",$vv);
            $this->Cell(0,7,substr($v,0,iconv('utf-8','cp1251',substr($v,0,$pos-1))),0,0,'L',$odd);
            $this->Ln(7);
            $this->Cell(88);
            $this->Cell(0,7,substr($v,iconv('utf-8','cp1251',substr($v,$pos-1))),0,0,'L',$odd);
        }else $this->Cell(0,14,iconv('utf-8','cp1251',$v),0,0,'L',$odd);
    }
    public function HeadingBlue($str){
        $this->SetFont('MyriadProBold','',14);
        $this->SetFillColor(20,133,204);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,16,iconv('utf-8','cp1251',$str),0,0,'C',true);
        $this->Ln(16);
    }
    public function HeadingGreen($str){
        $this->SetFont('MyriadProBold','',14);
        $this->SetFillColor(25, 182, 137);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,16,iconv('utf-8','cp1251',$str),0,0,'C',true);
        $this->Ln(16);
    }
    public function HeadingOrange($str){
        $this->SetFont('MyriadProBold','',14);
        $this->SetFillColor(204, 111, 20);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,16,iconv('utf-8','cp1251',$str),0,0,'C',true);
        $this->Ln(16);
    }
    protected function getCategory($c){
        $cc = preg_replace('/а/i','A',$c);
        $cc = preg_replace('/в/i','B',$cc);
        $cc = preg_replace('/с/i','C',$cc);
        $cc = preg_replace('/е/i','E',$cc);
        $cc = preg_replace('/м/','m',$cc);
        $cc = preg_replace('/М/','M',$cc);
        $cc = preg_replace('/т/i','T',$cc);
        $categories=[
            "А"=>"Мотоциклы",
            "А1"=>"Легкие мотоциклы",
            "В"=>"Легковые автомобили, небольшие грузовики (до 3,5 тонн)",
            "ВE"=>"Легковые автомобили с прицепом",
            "В1"=>"Трициклы",
            "С"=>"Грузовые автомобили (от 3,5 тонн)",
            "СE"=>"Грузовые автомобили с прицепом",
            "С1"=>"Средние грузовики (от 3,5 до 7,5 тонн)",
            "С1E"=>"Средние грузовики с прицепом",
            "D"=>"Автобусы",
            "DE"=>"Автобусы с прицепом",
            "D1"=>"Небольшие автобусы",
            "D1E"=>"Небольшие автобусы с прицепом",
            "М"=>"Мопеды",
            "Tm"=>"Трамваи",
            "Tb"=>"Троллейбусы"
        ];
        return $categories[$cc];
    }
    protected function getCountry($wmi){
        $wmiList = [
            "1B" => "Соединенные Штаты",
            "1C" => "Соединенные Штаты",
            "1D" => "Соединенные Штаты",
            "1E" => "Соединенные Штаты",
            "1F" => "Соединенные Штаты",
            "1G" => "Соединенные Штаты",
            "1H" => "Соединенные Штаты",
            "1I" => "Соединенные Штаты",
            "1J" => "Соединенные Штаты",
            "1K" => "Соединенные Штаты",
            "1L" => "Соединенные Штаты",
            "1M" => "Соединенные Штаты",
            "1N" => "Соединенные Штаты",
            "1O" => "Соединенные Штаты",
            "1P" => "Соединенные Штаты",
            "1Q" => "Соединенные Штаты",
            "1R" => "Соединенные Штаты",
            "1S" => "Соединенные Штаты",
            "1T" => "Соединенные Штаты",
            "1U" => "Соединенные Штаты",
            "1V" => "Соединенные Штаты",
            "1W" => "Соединенные Штаты",
            "1X" => "Соединенные Штаты",
            "1Y" => "Соединенные Штаты",
            "1Z" => "Соединенные Штаты",
            "11" => "Соединенные Штаты",
            "12" => "Соединенные Штаты",
            "13" => "Соединенные Штаты",
            "14" => "Соединенные Штаты",
            "15" => "Соединенные Штаты",
            "16" => "Соединенные Штаты",
            "17" => "Соединенные Штаты",
            "18" => "Соединенные Штаты",
            "19" => "Соединенные Штаты",
            "10" => "Соединенные Штаты",
            "2B" => "Канада",
            "2C" => "Канада",
            "2D" => "Канада",
            "2E" => "Канада",
            "2F" => "Канада",
            "2G" => "Канада",
            "2H" => "Канада",
            "2I" => "Канада",
            "2J" => "Канада",
            "2K" => "Канада",
            "2L" => "Канада",
            "2M" => "Канада",
            "2N" => "Канада",
            "2O" => "Канада",
            "2P" => "Канада",
            "2Q" => "Канада",
            "2R" => "Канада",
            "2S" => "Канада",
            "2T" => "Канада",
            "2U" => "Канада",
            "2V" => "Канада",
            "2W" => "Канада",
            "2X" => "Канада",
            "2Y" => "Канада",
            "2Z" => "Канада",
            "21" => "Канада",
            "22" => "Канада",
            "23" => "Канада",
            "24" => "Канада",
            "25" => "Канада",
            "26" => "Канада",
            "27" => "Канада",
            "28" => "Канада",
            "29" => "Канада",
            "20" => "Канада",
            "3B" => "Мексика",
            "3C" => "Мексика",
            "3D" => "Мексика",
            "3E" => "Мексика",
            "3F" => "Мексика",
            "3G" => "Мексика",
            "3H" => "Мексика",
            "3I" => "Мексика",
            "3J" => "Мексика",
            "3K" => "Мексика",
            "3L" => "Мексика",
            "3M" => "Мексика",
            "3N" => "Мексика",
            "3O" => "Мексика",
            "3P" => "Мексика",
            "3Q" => "Мексика",
            "3R" => "Мексика",
            "3S" => "Мексика",
            "3T" => "Мексика",
            "3U" => "Мексика",
            "3V" => "Мексика",
            "3W" => "Мексика",
            "3Y" => "Коста-Рика",
            "3Z" => "Коста-Рика",
            "31" => "Коста-Рика",
            "32" => "Коста-Рика",
            "33" => "Коста-Рика",
            "34" => "Коста-Рика",
            "35" => "Коста-Рика",
            "36" => "Коста-Рика",
            "37" => "Коста-Рика",
            "39" => "Каймановы острова",
            "30" => "Каймановы острова",
            "4B" => "Соединенные Штаты",
            "4C" => "Соединенные Штаты",
            "4D" => "Соединенные Штаты",
            "4E" => "Соединенные Штаты",
            "4F" => "Соединенные Штаты",
            "4G" => "Соединенные Штаты",
            "4H" => "Соединенные Штаты",
            "4I" => "Соединенные Штаты",
            "4J" => "Соединенные Штаты",
            "4K" => "Соединенные Штаты",
            "4L" => "Соединенные Штаты",
            "4M" => "Соединенные Штаты",
            "4N" => "Соединенные Штаты",
            "4O" => "Соединенные Штаты",
            "4P" => "Соединенные Штаты",
            "4Q" => "Соединенные Штаты",
            "4R" => "Соединенные Штаты",
            "4S" => "Соединенные Штаты",
            "4T" => "Соединенные Штаты",
            "4U" => "Соединенные Штаты",
            "4V" => "Соединенные Штаты",
            "4W" => "Соединенные Штаты",
            "4X" => "Соединенные Штаты",
            "4Y" => "Соединенные Штаты",
            "4Z" => "Соединенные Штаты",
            "41" => "Соединенные Штаты",
            "42" => "Соединенные Штаты",
            "43" => "Соединенные Штаты",
            "44" => "Соединенные Штаты",
            "45" => "Соединенные Штаты",
            "46" => "Соединенные Штаты",
            "47" => "Соединенные Штаты",
            "48" => "Соединенные Штаты",
            "49" => "Соединенные Штаты",
            "40" => "Соединенные Штаты",
            "5B" => "Соединенные Штаты",
            "5C" => "Соединенные Штаты",
            "5D" => "Соединенные Штаты",
            "5E" => "Соединенные Штаты",
            "5F" => "Соединенные Штаты",
            "5G" => "Соединенные Штаты",
            "5H" => "Соединенные Штаты",
            "5I" => "Соединенные Штаты",
            "5J" => "Соединенные Штаты",
            "5K" => "Соединенные Штаты",
            "5L" => "Соединенные Штаты",
            "5M" => "Соединенные Штаты",
            "5N" => "Соединенные Штаты",
            "5O" => "Соединенные Штаты",
            "5P" => "Соединенные Штаты",
            "5Q" => "Соединенные Штаты",
            "5R" => "Соединенные Штаты",
            "5S" => "Соединенные Штаты",
            "5T" => "Соединенные Штаты",
            "5U" => "Соединенные Штаты",
            "5V" => "Соединенные Штаты",
            "5W" => "Соединенные Штаты",
            "5X" => "Соединенные Штаты",
            "5Y" => "Соединенные Штаты",
            "5Z" => "Соединенные Штаты",
            "51" => "Соединенные Штаты",
            "52" => "Соединенные Штаты",
            "53" => "Соединенные Штаты",
            "54" => "Соединенные Штаты",
            "55" => "Соединенные Штаты",
            "56" => "Соединенные Штаты",
            "57" => "Соединенные Штаты",
            "58" => "Соединенные Штаты",
            "59" => "Соединенные Штаты",
            "50" => "Соединенные Штаты",
            "6B" => "Австралия",
            "6C" => "Австралия",
            "6D" => "Австралия",
            "6E" => "Австралия",
            "6F" => "Австралия",
            "6G" => "Австралия",
            "6H" => "Австралия",
            "6I" => "Австралия",
            "6J" => "Австралия",
            "6K" => "Австралия",
            "6L" => "Австралия",
            "6M" => "Австралия",
            "6N" => "Австралия",
            "6O" => "Австралия",
            "6P" => "Австралия",
            "6Q" => "Австралия",
            "6R" => "Австралия",
            "6S" => "Австралия",
            "6T" => "Австралия",
            "6U" => "Австралия",
            "6V" => "Австралия",
            "6W" => "Австралия",
            "7B" => "Новая Зеландия",
            "7C" => "Новая Зеландия",
            "7D" => "Новая Зеландия",
            "7E" => "Новая Зеландия",
            "8B" => "Аргентина",
            "8C" => "Аргентина",
            "8D" => "Аргентина",
            "8E" => "Аргентина",
            "8G" => "Чили",
            "8H" => "Чили",
            "8I" => "Чили",
            "8J" => "Чили",
            "8K" => "Чили",
            "8M" => "Эквадор",
            "8N" => "Эквадор",
            "8O" => "Эквадор",
            "8P" => "Эквадор",
            "8Q" => "Эквадор",
            "8R" => "Эквадор",
            "8T" => "Перу",
            "8U" => "Перу",
            "8V" => "Перу",
            "8W" => "Перу",
            "8Y" => "Венесуэла",
            "8Z" => "Венесуэла",
            "81" => "Венесуэла",
            "82" => "Венесуэла",
            "9B" => "Бразилия",
            "9C" => "Бразилия",
            "9D" => "Бразилия",
            "9E" => "Бразилия",
            "9G" => "Колумбия",
            "9H" => "Колумбия",
            "9I" => "Колумбия",
            "9J" => "Колумбия",
            "9K" => "Колумбия",
            "9M" => "Парагвай",
            "9N" => "Парагвай",
            "9O" => "Парагвай",
            "9P" => "Парагвай",
            "9Q" => "Парагвай",
            "9R" => "Парагвай",
            "9T" => "Тринидад и Тобаго",
            "9U" => "Тринидад и Тобаго",
            "9V" => "Тринидад и Тобаго",
            "9W" => "Тринидад и Тобаго",
            "9Y" => "Бразилия",
            "9Z" => "Бразилия",
            "91" => "Бразилия",
            "92" => "Бразилия",
            "AB" => "ЮАР",
            "AC" => "ЮАР",
            "AD" => "ЮАР",
            "AE" => "ЮАР",
            "AF" => "ЮАР",
            "AG" => "ЮАР",
            "AH" => "ЮАР",
            "BG" => "Кения",
            "BH" => "Кения",
            "BI" => "Кения",
            "BJ" => "Кения",
            "BK" => "Кения",
            "BM" => "Танзании",
            "BN" => "Танзании",
            "BO" => "Танзании",
            "BP" => "Танзании",
            "BQ" => "Танзании",
            "BR" => "Танзании",
            "CB" => "Бенин",
            "CC" => "Бенин",
            "CD" => "Бенин",
            "CE" => "Бенин",
            "CG" => "Мадагаскар",
            "CH" => "Мадагаскар",
            "CI" => "Мадагаскар",
            "CJ" => "Мадагаскар",
            "CK" => "Мадагаскар",
            "CM" => "Тунис",
            "CN" => "Тунис",
            "CO" => "Тунис",
            "CP" => "Тунис",
            "CQ" => "Тунис",
            "CR" => "Тунис",
            "DB" => "Египет",
            "DC" => "Египет",
            "DD" => "Египет",
            "DE" => "Египет",
            "DG" => "Марокко",
            "DH" => "Марокко",
            "DI" => "Марокко",
            "DJ" => "Марокко",
            "DK" => "Марокко",
            "DM" => "Замбия",
            "DN" => "Замбия",
            "DO" => "Замбия",
            "DP" => "Замбия",
            "DQ" => "Замбия",
            "DR" => "Замбия",
            "EB" => "Эфиопия",
            "EC" => "Эфиопия",
            "ED" => "Эфиопия",
            "EE" => "Эфиопия",
            "EG" => "Мозамбик",
            "EH" => "Мозамбик",
            "EI" => "Мозамбик",
            "EJ" => "Мозамбик",
            "EK" => "Мозамбик",
            "FB" => "Гана",
            "FC" => "Гана",
            "FD" => "Гана",
            "FE" => "Гана",
            "FM" => "Нигерия",
            "FN" => "Нигерия",
            "FO" => "Нигерия",
            "FP" => "Нигерия",
            "FQ" => "Нигерия",
            "FR" => "Нигерия",
            "FS" => "Нигерия",
            "FT" => "Нигерия",
            "FU" => "Нигерия",
            "FV" => "Нигерия",
            "FW" => "Нигерия",
            "FX" => "Нигерия",
            "FY" => "Нигерия",
            "FZ" => "Нигерия",
            "F1" => "Нигерия",
            "F2" => "Нигерия",
            "F3" => "Нигерия",
            "F4" => "Нигерия",
            "F5" => "Нигерия",
            "F6" => "Нигерия",
            "F7" => "Нигерия",
            "F8" => "Нигерия",
            "F9" => "Нигерия",
            "F0" => "Нигерия",
            "JB" => "Япония",
            "JC" => "Япония",
            "JD" => "Япония",
            "JE" => "Япония",
            "JF" => "Япония",
            "JG" => "Япония",
            "JH" => "Япония",
            "JI" => "Япония",
            "JJ" => "Япония",
            "JK" => "Япония",
            "JL" => "Япония",
            "JM" => "Япония",
            "JN" => "Япония",
            "JO" => "Япония",
            "JP" => "Япония",
            "JQ" => "Япония",
            "JR" => "Япония",
            "JS" => "Япония",
            "JT" => "Япония",
            "KB" => "Шри-Ланка",
            "KC" => "Шри-Ланка",
            "KD" => "Шри-Ланка",
            "KE" => "Шри-Ланка",
            "KG" => "Израиль",
            "KH" => "Израиль",
            "KI" => "Израиль",
            "KJ" => "Израиль",
            "KK" => "Израиль",
            "KM" => "Южная Корея",
            "KN" => "Южная Корея",
            "KO" => "Южная Корея",
            "KP" => "Южная Корея",
            "KQ" => "Южная Корея",
            "KR" => "Южная Корея",
            "LB" => "Тайвань - Китай",
            "LC" => "Тайвань - Китай",
            "LD" => "Тайвань - Китай",
            "LE" => "Тайвань - Китай",
            "LF" => "Тайвань - Китай",
            "LG" => "Тайвань - Китай",
            "LH" => "Тайвань - Китай",
            "LI" => "Тайвань - Китай",
            "LJ" => "Тайвань - Китай",
            "LK" => "Тайвань - Китай",
            "LL" => "Тайвань - Китай",
            "LM" => "Тайвань - Китай",
            "LN" => "Тайвань - Китай",
            "LO" => "Тайвань - Китай",
            "LP" => "Тайвань - Китай",
            "LQ" => "Тайвань - Китай",
            "LR" => "Тайвань - Китай",
            "LS" => "Тайвань - Китай",
            "LT" => "Тайвань - Китай",
            "LU" => "Тайвань - Китай",
            "LV" => "Тайвань - Китай",
            "LW" => "Тайвань - Китай",
            "LX" => "Тайвань - Китай",
            "LY" => "Тайвань - Китай",
            "LZ" => "Тайвань - Китай",
            "L1" => "Тайвань - Китай",
            "L2" => "Тайвань - Китай",
            "L3" => "Тайвань - Китай",
            "L4" => "Тайвань - Китай",
            "L5" => "Тайвань - Китай",
            "L6" => "Тайвань - Китай",
            "L7" => "Тайвань - Китай",
            "L8" => "Тайвань - Китай",
            "L9" => "Тайвань - Китай",
            "L0" => "Тайвань - Китай",
            "MB" => "Индия",
            "MC" => "Индия",
            "MD" => "Индия",
            "ME" => "Индия",
            "MG" => "Индонезия",
            "MH" => "Индонезия",
            "MI" => "Индонезия",
            "MJ" => "Индонезия",
            "MK" => "Индонезия",
            "MM" => "Таиланд",
            "MN" => "Таиланд",
            "MO" => "Таиланд",
            "MP" => "Таиланд",
            "MQ" => "Таиланд",
            "MR" => "Таиланд",
            "NG" => "Пакистан",
            "NH" => "Пакистан",
            "NI" => "Пакистан",
            "NJ" => "Пакистан",
            "NK" => "Пакистан",
            "NL" => "Турция",
            "NM" => "Турция",
            "NN" => "Турция",
            "NO" => "Турция",
            "NP" => "Турция",
            "NQ" => "Турция",
            "NR" => "Турция",
            "PB" => "Филиппины",
            "PC" => "Филиппины",
            "PD" => "Филиппины",
            "PE" => "Филиппины",
            "PG" => "Сингапур",
            "PH" => "Сингапур",
            "PI" => "Сингапур",
            "PJ" => "Сингапур",
            "PK" => "Сингапур",
            "PM" => "Малайзия",
            "PN" => "Малайзия",
            "PO" => "Малайзия",
            "PP" => "Малайзия",
            "PQ" => "Малайзия",
            "PR" => "Малайзия",
            "RB" => "Объединенные Арабские Эмираты",
            "RC" => "Объединенные Арабские Эмираты",
            "RD" => "Объединенные Арабские Эмираты",
            "RE" => "Объединенные Арабские Эмираты",
            "RG" => "Тайвань",
            "RH" => "Тайвань",
            "RI" => "Тайвань",
            "RJ" => "Тайвань",
            "RK" => "Тайвань",
            "RM" => "Вьетнам",
            "RN" => "Вьетнам",
            "RO" => "Вьетнам",
            "RP" => "Вьетнам",
            "RQ" => "Вьетнам",
            "RR" => "Вьетнам",
            "SB" => "Великобритания",
            "SC" => "Великобритания",
            "SD" => "Великобритания",
            "SE" => "Великобритания",
            "SF" => "Великобритания",
            "SG" => "Великобритания",
            "SH" => "Великобритания",
            "SI" => "Великобритания",
            "SJ" => "Великобритания",
            "SK" => "Великобритания",
            "SL" => "Великобритания",
            "SM" => "Великобритания",
            "SO" => "Германия",
            "SP" => "Германия",
            "SQ" => "Германия",
            "SR" => "Германия",
            "SS" => "Германия",
            "ST" => "Германия",
            "SV" => "Польша",
            "SW" => "Польша",
            "SX" => "Польша",
            "SY" => "Польша",
            "SZ" => "Польша",
            "S2" => "Латвия",
            "S3" => "Латвия",
            "S4" => "Латвия",
            "TB" => "Швейцария",
            "TC" => "Швейцария",
            "TD" => "Швейцария",
            "TE" => "Швейцария",
            "TF" => "Швейцария",
            "TG" => "Швейцария",
            "TH" => "Швейцария",
            "TI" => "Чехия",
            "TK" => "Чехия",
            "TL" => "Чехия",
            "TM" => "Чехия",
            "TN" => "Чехия",
            "TO" => "Чехия",
            "TP" => "Чехия",
            "TS" => "Венгрия",
            "TT" => "Венгрия",
            "TU" => "Венгрия",
            "TV" => "Венгрия",
            "TX" => "Португалия",
            "TY" => "Португалия",
            "TZ" => "Португалия",
            "T1" => "Португалия",
            "UF" => "Дания",
            "UI" => "Дания",
            "UJ" => "Дания",
            "UK" => "Дания",
            "UL" => "Дания",
            "UM" => "Дания",
            "UO" => "Ирландия",
            "UP" => "Ирландия",
            "UQ" => "Ирландия",
            "UR" => "Ирландия",
            "US" => "Ирландия",
            "UT" => "Ирландия",
            "UV" => "Румыния",
            "UW" => "Румыния",
            "UX" => "Румыния",
            "UY" => "Румыния",
            "UZ" => "Румыния",
            "U6" => "Словакия",
            "U7" => "Словакия",
            "U9" => "Словакия",
            "U0" => "Словакия",
            "VB" => "Австрия",
            "VC" => "Австрия",
            "VD" => "Австрия",
            "VE" => "Австрия",
            "VF" => "Франция",
            "VG" => "Франция",
            "VH" => "Франция",
            "VI" => "Франция",
            "VJ" => "Франция",
            "VK" => "Франция",
            "VL" => "Франция",
            "VM" => "Франция",
            "VN" => "Франция",
            "VO" => "Франция",
            "VP" => "Франция",
            "VQ" => "Франция",
            "VR" => "Франция",
            "VT" => "Испания",
            "VU" => "Испания",
            "VV" => "Испания",
            "VW" => "Испания",
            "VY" => "Сербия",
            "VZ" => "Сербия",
            "V1" => "Сербия",
            "V2" => "Сербия",
            "V4" => "Хорватия",
            "V5" => "Хорватия",
            "V7" => "Эстония",
            "V8" => "Эстония",
            "V9" => "Эстония",
            "V0" => "Эстония",
            "WB" => "Германия",
            "WC" => "Германия",
            "WD" => "Германия",
            "WE" => "Германия",
            "WF" => "Германия",
            "WG" => "Германия",
            "WH" => "Германия",
            "WI" => "Германия",
            "WJ" => "Германия",
            "WK" => "Германия",
            "WL" => "Германия",
            "WM" => "Германия",
            "WN" => "Германия",
            "WO" => "Германия",
            "WP" => "Германия",
            "WQ" => "Германия",
            "WR" => "Германия",
            "WS" => "Германия",
            "WT" => "Германия",
            "WU" => "Германия",
            "WV" => "Германия",
            "WW" => "Германия",
            "WX" => "Германия",
            "WY" => "Германия",
            "WZ" => "Германия",
            "W1" => "Германия",
            "W2" => "Германия",
            "W3" => "Германия",
            "W4" => "Германия",
            "W5" => "Германия",
            "W6" => "Германия",
            "W7" => "Германия",
            "W8" => "Германия",
            "W9" => "Германия",
            "W0" => "Германия",
            "XB" => "Болгария",
            "XC" => "Болгария",
            "XD" => "Болгария",
            "XE" => "Болгария",
            "XG" => "Греция",
            "XH" => "Греция",
            "XI" => "Греция",
            "XJ" => "Греция",
            "XK" => "Греция",
            "XM" => "Нидерланды",
            "XN" => "Нидерланды",
            "XO" => "Нидерланды",
            "XP" => "Нидерланды",
            "XQ" => "Нидерланды",
            "XR" => "Нидерланды",
            "XT" => "СССР (СНГ)",
            "XU" => "СССР (СНГ)",
            "XV" => "СССР (СНГ)",
            "XW" => "СССР (СНГ)",
            "XY" => "Люксембург",
            "XZ" => "Люксембург",
            "X1" => "Люксембург",
            "X2" => "Люксембург",
            "X4" => "Россия",
            "X5" => "Россия",
            "X6" => "Россия",
            "X7" => "Россия",
            "X8" => "Россия",
            "X9" => "Россия",
            "X0" => "Россия",
            "YB" => "Бельгия",
            "YC" => "Бельгия",
            "YD" => "Бельгия",
            "YE" => "Бельгия",
            "YG" => "Финляндия",
            "YH" => "Финляндия",
            "YI" => "Финляндия",
            "YJ" => "Финляндия",
            "YK" => "Финляндия",
            "YM" => "Мальта",
            "YN" => "Мальта",
            "YO" => "Мальта",
            "YP" => "Мальта",
            "YQ" => "Мальта",
            "YR" => "Мальта",
            "YT" => "Швеция",
            "YU" => "Швеция",
            "YV" => "Швеция",
            "YW" => "Швеция",
            "YY" => "Норвегия",
            "YZ" => "Норвегия",
            "Y1" => "Норвегия",
            "Y2" => "Норвегия",
            "Y4" => "Белоруссия",
            "Y5" => "Белоруссия",
            "Y7" => "Украина",
            "Y8" => "Украина",
            "Y9" => "Украина",
            "Y0" => "Украина",
            "ZB" => "Италия",
            "ZC" => "Италия",
            "ZD" => "Италия",
            "ZE" => "Италия",
            "ZF" => "Италия",
            "ZG" => "Италия",
            "ZH" => "Италия",
            "ZI" => "Италия",
            "ZJ" => "Италия",
            "ZK" => "Италия",
            "ZL" => "Италия",
            "ZM" => "Италия",
            "ZN" => "Италия",
            "ZO" => "Италия",
            "ZP" => "Италия",
            "ZQ" => "Италия",
            "ZR" => "Италия",
            "ZY" => "Словения",
            "ZZ" => "Словения",
            "Z1" => "Словения",
            "Z2" => "Словения",
            "Z4" => "Литва",
            "Z5" => "Литва",
            "Z8" => "Россия",
            "Z9" => "Россия",
            "Z0" => "Россия"
        ];
        if(strlen($wmi)>2)$wmi=substr($wmi,0,2);
        return isset($wmiList[$wmi])?$wmiList[$wmi]:"";
    }
};

?>
