<?php
class THelpdesk {
    public $url="";
    public $token="";
    public function __construct($url,$token) {
        $this->url=$url;
        $this->token=$token;
    }        
  public function reqwest($path,$body=[],$type="POST"){
        $curl = curl_init();
        if (count($body)==0){
            curl_setopt_array($curl, array(
              CURLOPT_URL => $this->url.$path,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                'Token: '.$this->token
              ),
            ));
        } else {            
            curl_setopt_array($curl, array(
              CURLOPT_URL => $this->url.$path,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => $type,
              CURLOPT_POSTFIELDS => $body,
              CURLOPT_HTTPHEADER => array(
                'Token: '.$this->token
              ),
            ));            
        };
        $response = curl_exec($curl);
        return $response;
  }  
  /**
   * Получить список пользователей HelpDesk
   * @return type
   */
  function ListUsers(){
     return $this->reqwest("/api/users",[]);
  }  
  /**
   * Создать нового пользователя HelpDesk
   * @param type $fullname
   * @param type $email
   * @param type $notify 1/0 1- прислать уведомление о создании
   * @return type
   */
  function CreateUser($fullname,$email,$notify="0"){
    return $this->reqwest("/api/users/create",[
        "fullname"=>$fullname,
        "email"=>$email,
        "notify"=>$notify
     ]);  
  }
  /**
   * Получить информацию по id пользователя HelpDesk
   * @param type $id
   * @return type
   */
  function GetUserInfo($id){
    return $this->reqwest("/api/users/show/$id",[]);  
  }
  /**
   * Обновить информацию о пользователе
   * @param type $id
   * @param type $new_email
   * @return type
   */  
  function UpdateUserInfo($id,$new_email){
    return $this->reqwest("/api/users/update/$id",["new_email"=>$new_email]);  
  }    
  /**
   * Удалить пользователя HelpDesk
   * @param type $id
   * @return type
   */
  function DeleteUser($id){
    return $this->reqwest("/api/users/delete/$id",[]);  
  }
  /**
   * Получить список отделов
   * @return type
   */
  function GetDepartamets(){
    return $this->reqwest("/api/departments",[]);  
  }
 /**
  * Получить информацию об отделе
  * @param type $id
  * @return type
  */ 
 function GetInfoDepartamet($id){
    return $this->reqwest("/api/departments/show/$id",[]);  
 }  
 /**
  * Получить список тикетов
  * @param type $dep_id  (опционально)
  * @param type $user_id (опционально) 
  * @param type $status  (опционально) 1: Open, 2: Answered, 3: Awaiting reply, 4: In progress, 5: Closed
  * @return type
  */
 function GetListTickets($dep_id=null,$user_id=null,$status=null){
    $body=[];    
    if (!is_null($dep_id)) $body["department_id"]=$dep_id;
    if (!is_null($user_id)) $body["user_id"]=$user_id;
    if (!is_null($status)) $body["status"]=$status;
    $get=http_build_query($body);    
    if ($get!="") $get="?".$get;
    return $this->reqwest("/api/tickets$get",$body,"GET");  
 }  
/**
 * Получить информацио по id тикета
 * @param type $id
 * @return type
 */ 
 function GetTicketInfo($id){
   return $this->reqwest("/api/tickets/show/$id",[]);    
 } 
 /**
  * Создать "пользовательский" тикет
  * @param type $user_id
  * @param type $dep_id
  * @param type $subject
  * @param type $mess
  * @param type $filename  (необязательно)
  * @param type $notify  (необязательно) 1 - уведомить пользователя
  * @return type
  */
 function CreateUserTicket($user_id,$dep_id,$subject,$mess,$filename=null,$notify=0){
     
   $body["opener"]="user";
   $body["user_id"]=$user_id;
   //$body["staff_id"]=$user_id;
   $body["department_id"]=$dep_id;
   $body["subject"]=$subject;
   $body["body"]=$mess;
   if (!is_null($filename)):
       $body["attachment[]"]=new CURLFILE($filename);
   else:
       $body["attachment[]"]=null; 
   endif;
   $body["notify"]=$notify;
   return $this->reqwest("/api/tickets/create",$body);      
 }
 /**
  * Создать "админский" тикет для конкретного пользователя
  * @param type $staff_id
  * @param type $user_id
  * @param type $dep_id
  * @param type $subject
  * @param type $mess
  * @param type $filename  (необязательно)
  * @param type $notify  (необязательно) 1- уведомить пользователя
  * @return type
  */
 function CreateStaffTicket($staff_id,$user_id,$dep_id,$subject,$mess,$filename=null,$notify=0){
     
   $body["opener"]="staff";
   $body["user_id"]=$user_id;
   $body["staff_id"]=$staff_id;
   $body["department_id"]=$dep_id;
   $body["subject"]=$subject;
   $body["body"]=$mess;
   if (!is_null($filename)):
       $body["attachment[]"]=new CURLFILE($filename);
   else:
       $body["attachment[]"]=null; 
   endif;
   $body["notify"]=$notify;   
   return $this->reqwest("/api/tickets/create",$body);      
 } 
/**
 * Оставить сообщение в тикете от имени пользователя
 * @param type $ticket_id
 * @param type $message
 * @param type $filename (необязательно)
 * @return type
 */ 
function AddUserTicketMessage($ticket_id,$message,$filename=null){     
   $body["ticket_id"]=$ticket_id;
   $body["replier"]="user";
   $body["message"]=$message;
   if (!is_null($filename)):
       $body["attachment[]"]=new CURLFILE($filename);
   else:
       $body["attachment[]"]=null;
   endif;
   $body["close"]="0";   
   return $this->reqwest("/api/messages/create",$body);      
 }  
/**
 * Оставить сообщение в тикете от имени администратора
 * @param type $ticket_id
 * @param type $staff_id - ид админа
 * @param type $message
 * @param type $filename (необязательно)
 * @param type $close (необязательно) 1 - закрыть тикет
 * @return type
 */ 
function AddStaffTicketMessage($ticket_id,$staff_id,$message,$filename=null,$close=0){     
   $body["ticket_id"]=$ticket_id;
   $body["replier"]="staff";
   $body["staff_id"]="$staff_id";
   $body["message"]=$message;
   if (!is_null($filename)):
       $body["attachment[]"]=new CURLFILE($filename);
   else:
       $body["attachment[]"]=null;
   endif;
   $body["close"]=$close;   
   return $this->reqwest("/api/messages/create",$body);      
 } 
/**
 * Получить список сообщений к тикету
 * @param type $ticket_id
 * @return type
 */ 
function GetListTicketMessages($ticket_id){     
   return $this->reqwest("/api/messages/show/$ticket_id",[]);      
}
/**
 * Получить список вложений файлов к тикету или сообщению
 * @param type $ticket_id (необязательно)
 * @param type $msg_id (необязательно)
 * @return type
 */
function GetListAttachment($ticket_id=null,$msg_id=null){
  $body=[];  
  if (!is_null($ticket_id)) $body["ticket_id"]=$ticket_id;
  if (!is_null($msg_id)) $body["msg_id"]=$msg_id;
  $get=http_build_query($body);    
  return $this->reqwest("/api/attachments?$get",$body,"GET");        
}
/**
 * Получить файл по его id
 * @param type $attach_id
 * @return type
 */
function GetContentAttachment($attach_id){
 return $this->reqwest("/api/attachments/show/$attach_id",[]);         
}
}

//$hh=new THelpdesk("https://help.xn--90acbu5aj5f.xn--p1ai","wrfewrfe");

//$res=$hh->CreateUserTicket(2,2,"Тикет из API","Создали тикет из АПИ с вложением","/home/user/whitelist.txt");
//$res=$hh->CreateStaffTicket(1,2,2,"Тикет из API без вложения","Создали тикет из АПИ");
//$res=$hh->GetContentAttachment(5);
//var_dump($res);
//
