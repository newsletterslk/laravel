<?php
namespace Newsletterslk;

use Illuminate\Support\Facades\Http;


class WebSMS 
{
    private $country_code="94";//Default Country Code Sri Lanka //94 with out +
    protected $url='https://app.newsletters.lk/smsAPI?';// ALWAYS USE THIS LINK TO CALL API SERVICE
    public $msgType="sms";// Message type sms/voice/unicode/flash/music/mms/whatsapp
    public $route=0;// Your Routing Path Default 0
    public $file=false;// File URL for voice or whatsapp. Default not set
    public $scheduledate=false;//Date and Time to send message (YYYY-MM-DD HH:mm:ss) Default not use
    public $duration=false;//Duration of your voice message in seconds (required for voice)
    public $language=false;//Language of voice message (required for text-to-speach)
    
    private function Call($params){
        $response = Http::get($this->url.$params);
        return $response->json();
    }

    public function RouteNumber($number){
        if($number){
            $explode=str_split($number);
            if($explode[0]=="+"){
                unset($explode[0]);
                $number=implode("",$explode);
            }else{
                if($explode[0]==0){
                    unset($explode[0]);
                    $number=implode("",$explode);
                }
                $number=$this->country_code.$number;
            }
            return $number;
        }else{
            return false;
        }
    }

    public static function CheckBalance($json=FALSE){
        $param='balance&apikey='.$this->user_key.'&apitoken='.$this->user_token;
        if($result=$this->Call($param)){
            if($json===FALSE){
                $c=json_decode($result);
                if($c['status']=="error"){
                    return false;
                }else{
                    return $c;
                }
            }else{
                return $result;
            }
        }else{
            return false;
        }
    }

    /**
     * Check SMS status
     * group_id = The group_id returned by send sms request
     * @return array
     */
    public static function CheckStatus($group_id,$json=FALSE){
        if($group_id){
            $param="&groupstatus&apikey=".$this->user_key."&apitoken=".$this->user_token."&groupid=".$group_id;
            if($res=$this->Call($param)){
                if($json===FALSE){
                    $c=json_decode($res);//You can also use direct json by call json as true
                    if($c['status']=="error"){
                        return false;
                    }else{
                        return $c;
                    }
                }else{
                    return $res;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function send_sms($number,$text,$json=true)
    {
        if(env('WEB_SMS_API') == null){
            if($json == true){
                return response(['status'=>0,'msg'=>'API key not detected, please conact us']);
            }else{
                return ['status'=>0,'msg'=>'API key not detected, please conact us'];
            }
        }
        if(env('WEB_SMS_TOKEN') == null){
            if($json == true){
                return response(['status'=>0,'msg'=>'API token not detected, please conact us']);
            }else{
                return ['status'=>0,'msg'=>'API token not detected, please conact us'];
            }
        }
        if(env('WEB_SMS_SENDER_ID') == null){
            if($json == true){
                return response(['status'=>0,'msg'=>'Sender ID not detected, please conact us']);
            }else{
                return ['status'=>0,'msg'=>'Sender ID not detected, please conact us'];
            }
        }
        return $this->SendMessage($number,$text,$json);
    }
    /**
     * Send Message
     * @return boolen
     */
    public function SendMessage($Mobile,$TEXT,$json=FALSE){
        $TEXT=urlencode($TEXT);
        if($this->sender_id !="" && $this->user_key !="" && $this->user_token !=""){
            if($Mobile){
                if($TEXT){
                    $Mobile=$this->RouteNumber($Mobile);
                    $param='sendsms&apikey='.$this->user_key.'&apitoken='.$this->user_token.'&from='.$this->sender_id.'&to='.$Mobile.'&type='.$this->msgType;if($this->route != 0) $param.='&route='.$this->route;
                    if($this->msgType=="sms" || $this->msgType=="unicode"){
                        //SMS
                       $param.='&text='.$TEXT;
                    }elseif($this->msgType=="voice" || $this->msgType=="mms"){
                        //Voice And MMS
                        if($this->file){
                            $param.='&text='.$TEXT.'&file='.$this->file;
                            if($this->msgType=="voice" && $this->duration !=false){
                                $param.='&duration='.$this->duration;
                            }
                        }else{
                            return false;
                        }
                    }elseif($this->msgType=="whatsapp"){
                        //WhatsAPP
                        $param.='&text='.$TEXT;
                        if($this->file){
                            $param.='&file='.$this->file;
                        }
                    }elseif($this->msgType=="flash"){
                        //Flash
                        $param.='&text='.$TEXT;
                        if($this->file){
                            $param.='&file='.$this->file;
                        }
                    }
                    if($this->scheduledate!=false){
                        $param.='&scheduledate='.$this->scheduledate;
                    }
                    if($this->language!=false){
                        $param.='&language='.$this->language;
                    }
                    if($res=$this->Call($param)){ 
                        if($json !=FALSE){
                            return $res;
                        }else{
                            $c=json_decode($res);
                            return $c;
                        }
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
?>