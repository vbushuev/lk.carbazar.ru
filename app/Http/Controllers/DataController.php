<?php
namespace App\Http\Controllers;

use Log;
use DB;
use App\Account as Account;
use App\Apikey as Apikey;
use App\User;
use Guzzle\Http\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Request as VinRequest;
use App\carbazar\Pdf;
class DataController extends Controller
{
    public function index(Request $rq){
        $res = [];
        $user = $rq->user();
        //"SELECT * FROM cb_requests join cb_sessions on cb_sessions.id=cb_requests.session_id join cb_clients on cb_clients.id = cb_sessions.client_id";
        $sel= DB::table("cb_requests")
            ->join("cb_sessions","cb_sessions.id","=","cb_requests.session_id")
             ->join("cb_clients","cb_clients.id","=","cb_sessions.client_id")
            ->join("cb_apikeys","cb_apikeys.id","=","cb_sessions.apikey_id")
            ->join("cb_accounts","cb_accounts.id","=","cb_clients.account_id")
            ->orderBy('cb_requests.id','desc')
            ->select("cb_requests.id",
                "cb_requests.created_at",
                "cb_requests.updated_at",
                "cb_requests.session_id",
                "cb_requests.code",
                "cb_requests.status",
                "cb_requests.message",
                "cb_requests.vin",
                "cb_requests.data",
                "cb_sessions.created_at as session_created_at",
                "cb_sessions.updated_at as session_updated_at",
                //DB::raw('select count(uploads.id) as total, sum(amount*quantity) as summary from uploads where uploads.transaction_id = upload_transactions.id'),
                "cb_clients.login",
                "cb_clients.name as user_name",
                "cb_clients.email as email"
                //,"(select count(*) from uploads where transaction_id = id) as total_counted"
            );
        $sel->where("cb_apikeys.account_id","=",$user->account_id);
        $statuses = $rq->input("status",['success','progress']);
        $sel->whereIn("cb_requests.status",$statuses);
        if($user->type == 'user')$sel->where("cb_sessions.client_id","=",$user->id);
        else if($user->type == 'admin')$sel->where("cb_apikeys.id","=",$user->apikey_id);
        else if($user->type == 'owner')$sel->where("cb_accounts.id","=",$user->account_id);
        //if($rq->input("status",false)!==false)$sel->where("cb_requests.status","=",$rq->input("status"));
        if($rq->input("from_date",false)!==false && preg_match("/\d{4}\-\d{2}\-\d{2}.*/",$rq->input("from_date")))$sel->whereDate("cb_requests.created_at",">=",$rq->input("from_date"));
        if($rq->input("to_date",false)!==false && preg_match("/\d{4}\-\d{2}\-\d{2}.*/",$rq->input("to_date")))$sel->whereDate("cb_requests.created_at","<=",$rq->input("to_date"));
        Log::debug($sel->toSql());
        $res = $sel->offset($rq->input("from",0))->limit($rq->input("to",24))->get();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function csv(Request $rq){
        $res = [];
        $user = $rq->user();
        //"SELECT * FROM cb_requests join cb_sessions on cb_sessions.id=cb_requests.session_id join cb_clients on cb_clients.id = cb_sessions.client_id";
        $sel= DB::table("cb_requests")
            ->join("cb_sessions","cb_sessions.id","=","cb_requests.session_id")
            ->join("cb_clients","cb_clients.id","=","cb_sessions.client_id")
            ->join("cb_apikeys","cb_apikeys.id","=","cb_sessions.apikey_id")
            ->orderBy('cb_requests.id','desc')
            ->select("cb_requests.id",
                "cb_requests.created_at",
                "cb_requests.updated_at",
                "cb_requests.session_id",
                "cb_requests.code",
                "cb_requests.status",
                "cb_requests.message",
                "cb_requests.vin",
                "cb_requests.data",
                "cb_sessions.created_at as session_created_at",
                "cb_sessions.updated_at as session_updated_at",
                //DB::raw('select count(uploads.id) as total, sum(amount*quantity) as summary from uploads where uploads.transaction_id = upload_transactions.id'),
                "cb_clients.login",
                "cb_clients.name as user_name",
                "cb_clients.email as email"
                //,"(select count(*) from uploads where transaction_id = id) as total_counted"
            );
        $sel->where("cb_apikeys.account_id","=",$user->account_id);
        $sel->where("cb_requests.code","=","200");
        $sel->where("cb_requests.status","=","success");
        if($rq->input("from_date",false)!==false && preg_match("/\d{4}\-\d{2}\-\d{2}.*/",$rq->input("from_date")))$sel->whereDate("cb_requests.created_at",">=",$rq->input("from_date"));
        if($rq->input("to_date",false)!==false && preg_match("/\d{4}\-\d{2}\-\d{2}.*/",$rq->input("to_date")))$sel->whereDate("cb_requests.created_at","<=",$rq->input("to_date"));
        Log::debug($sel->toSql());
        $res = $sel->offset($rq->input("from",0))->limit($rq->input("to",24))->get();
        $comma = ';';
        $csv = "Created".$comma."Login".$comma."VIN".$comma."Report".$comma."\n";
        foreach ($res as $row) {
            $csv.= $row->created_at.$comma.$row->login.$comma.$row->vin.$comma."http://lk.cars-bazar.ru/data/pdf?id=".$row->id.$comma."\n";
        }
        return response($csv)->withHeaders([
            'Content-Type' => 'application/csv; charset=utf-8',
            'Content-disposition'=>' attachment;filename=carsbazar.csv'
        ]);
    }
    public function pdf(Request $rq){
        $request = VinRequest::find($rq->input("id"));
        $domain = request()->getHost();
        $report = $request->data;
        $report = json_decode($report,true);
        // print_r($report);
        $pdf = new Pdf;
        if(isset($report["response"]["history"])) $reportPdf = $pdf->Report($report["response"],$domain);
        return response($reportPdf)
            ->withHeaders([
                'Content-Type' => 'application/pdf; charset=utf-8',

            ]);
        return ;
    }
    public function vin(Request $rq){
        $vin = $rq->input("vin",false);
        $res = [];
        if($vin!=false){
            $host = "http://api.".preg_replace("/lk\./i","",request()->getHost())."/";
            $user = $rq->user();
            $apikey = Apikey::find($user->apikey_id);
            //$sel= DB::table("cb_apikeys")->where("cb_apikeys.id","=",$user->apikay_id)->select("apikey")->first();
            $client = new HttpClient();
            $response = $client->get($host."auth?login=".$user->login."&password=".$user->password."&apikey=".$apikey->apikey)->send();
            if($response->getStatusCode()=="200"){
                $authJSON = json_decode($response->getBody(),true); // 200
                $session = $authJSON["response"]["session"];
                $response = $client->get($host."request?session=".$authJSON["response"]["session"]."&vin=".$vin,null,['timeout' => 300])->send();
                // $response = $client->request('GET', $host."request?session=".$authJSON["response"]["session"]."&vin=".$vin, ['timeout' => 300]);
                if($response->getStatusCode()=="200"){
                    $res=json_decode($response->getBody());
                }
            }

            //
            // $authJSON = json_decode(file_get_contents($host."auth?login=".$user->login."&password=".$user->password."&apikey=".$sel->apikey),true);
            // $session = $authJSON["response"]["session"];
            // $res = json_decode(file_get_contents($host."request?session=".$authJSON["response"]["session"]."&vin=".$vin),true);
        }
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function clientinfo(Request $rq){
        $user = $rq->user();
        $res = $user->toArray();
        $account = DB::table("cb_accounts")->join("cb_clients","cb_accounts.id","=","cb_clients.account_id")
            ->where("cb_apikeys.account_id","=",$user->account_id)
            ->get();
        $res = array_merge($res,$account->toArray());
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    // public function clientUpdate(Request $rq){
    //     $data = $rq->all();
    //     $res = User::find($data["id"]);
    //     $res->fill([
    //         'name' => isset($data['name'])?$data['name']:$res->name,
    //         'email' => isset($data['email'])?$data['email']:$res->email,
    //         'password' => bcrypt($data['password']),
    //         'type' => isset($data["type"])?$data["type"]:$res->type,
    //         'login' => isset($data["login"])?$data["login"]:$res->login,
    //         'account_id'=> isset($data["account_id"])?$data["account_id"]:$res->account_id
    //     ]);
    //     $res->save();
    //     return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    // }
    public function acountinfo(Request $rq){
        $user = $rq->user();
        $res = DB::table("cb_accounts")//->join("cb_clients","cb_accounts.id","=","cb_clients.account_id")
            ->where("cb_accounts.id","=",$user->account_id)
            ->get();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function acountlist(Request $rq){
        //$user = $rq->user();
        $res = DB::table("cb_accounts")//->join("cb_clients","cb_accounts.id","=","cb_clients.account_id")
            //->where("cb_accounts.id","=",$user->account_id)
            ->get();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function acountadd(Request $rq){
        $res = DB::table("cb_accounts")->insertGetId(["name"=>$rq->input("name","_name_"),"quantity"=>$rq->input("quantity",100)]);
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function acountupdate(Request $rq){
        $res = Account::find($rq->input("id",0));
        $res->fill([
            "name"=>$rq->input("name"),"quantity"=>$rq->input("quantity")
        ])->save();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function apikeyinfo(Request $rq){
        $user = $rq->user();
        $res= DB::table("cb_apikeys")
            ->where("cb_apikeys.account_id","=",$user->account_id)
            ->get();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function apikeylist(Request $rq){
        $ak = ($rq->input("account_id",false)!==false)?Apikey::where("account_id",$rq->input("account_id"))->get():Apikey::all();
        $res = [];
        foreach ($ak as $key) {
            $acc = Account::find($key->account_id);
            $res[]=[
                "id"=>$key->id,
                "apikey"=>$key->apikey,
                "account_id"=>$key->account_id,
                "account"=>$acc->name
            ];
        }
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function apikeyadd(Request $rq){
        $res = Apikey::create([
            "apikey"=>self::generateApikey(),
            "account_id"=>$rq->input("account_id")
        ]);
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function apikeyupdate(Request $rq){
        $res = Apikey::find($rq->input("id",0));
        $res->fill([
            "account_id"=>$rq->input("account_id")
        ])->save();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public static function generateApikey(){
        if (function_exists('com_create_guid') === true) return trim(com_create_guid(), '{}');
        return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function clientlist(Request $rq){
        $us = User::get();
        $res = [];
        foreach ($us as $key) {
            $acc = Account::find($key->account_id);
            $ak = Apikey::find($key->apikey_id);
            $res[]=array_merge($key->toArray(),[
                "account"=>$acc->name,
                "apikey"=>$ak->apikey
            ]);
        }
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function clientremove(Request $rq){
        $res = User::find($rq->input("id",0))->delete();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function clientadd(Request $rq){
        $data = $rq->all();
        $data["password"] = bcrypt($data["password"]);
        $res = User::create($data);
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    public function clientupdate(Request $rq){
        $res = User::find($rq->input("id",0));
        $data = $rq->all();
        if(isset($data["password"]))$data["password"] = bcrypt($data["password"]);
        unset($data["id"]);
        $res->fill($data)->save();
        return response()->json($res,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}
