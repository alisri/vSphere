<?php
    namespace vsphere;
    class vm{

        private $connection;
        public $vm;
        public static $instance=[];
        public static function makeVmInstance(connection $connection,$properties,$vm=null){
            static::$instance=[];

            foreach ($properties as $items)
            {
                if(gettype($items)=="array")
                {
                    foreach ($items as $item)
                    {
                        static::$instance[]=new static($connection,$item,$vm);
                    }
                }
                else{
                        static::$instance[]=new static($connection,$items,$vm);
                    }

            }
            return (count(static::$instance)==1) ? static::$instance[0] :static::$instance;

        }


        private function __construct(connection $connection,$items,$vm=null)
        {
            $this->connection=$connection;
            $this->vm=$vm;
            $this->parseObject($items);
        }
        private function parseObject($items){
                foreach ($items as $key=>$value)
                {
                    $this->$key=$value;
                }
        }

        public function makeFull(){
            $vm=$this->connection->makeRequest($this->connection::GET,"vcenter/vm/".$this->vm,false);
            return vm::makeVmInstance($this->connection,json_decode($vm->getBody(),$this->vm));
        }



        public function __set($name, $value)
        {
            $this->$name=$value;
        }

        public function getVmStatus(){
            return $this->connection->makeRequest(connection::GET,"/vcenter/vm/$this->vm/power",false);
        }


        public function turnOffServer(){
            if($this->canUpdateVmStatus())
            {
                $this->power_state="POWERED_OFF";
                $response=$this->connection->makeRequest(connection::POST,"/vcenter/vm/$this->vm/power/stop",false);
                if((string) $response->getBody() === '' &&  $response->getStatusCode() >= 200){
                    return new Response("true",__METHOD__,"operation was successful");
                }
                return false;
            }
            return "you cant".__METHOD__." bc the server is not running";

        }


        public function resetServer(){
            if($this->canUpdateVmStatus())
            {
                $this->power_state="POWERED_ON";
                $response=$this->connection->makeRequest(connection::POST,"/vcenter/vm/$this->vm/power/reset",false);
                if((string) $response->getBody() === '' &&  $response->getStatusCode() >= 200){
                    return new Response("true",__METHOD__,"operation was successful");
                }
                return false;

            }
            return new Response("false",__METHOD__,"only when vm is power on is working");
        }

        public function turnOnServer(){
            if(!$this->canUpdateVmStatus())
            {
                $this->power_state="POWERED_ON";
                $response=$this->connection->makeRequest(connection::POST,"/vcenter/vm/$this->vm/power/start",false);
                if((string) $response->getBody() === '' &&  $response->getStatusCode() >= 200){
                    return new Response("true",__METHOD__,"operation was successful");
                }
                return false;
            }
            return new Response("false",__METHOD__,"only when vm is power off or suspend is working");
        }
        public function suspendServer(){
            if($this->canUpdateVmStatus()){
                $this->power_state="SUSPENDED";
                $response= $this->connection->makeRequest(connection::POST,"/vcenter/vm/$this->vm/power/suspend",false);
                if((string) $response->getBody() === '' &&  $response->getStatusCode() >= 200){
                    return new Response("true",__METHOD__,"operation was successful");
                }
                return false;
            }
            return new Response("false",__METHOD__,"only when vm is power on is working");
        }


        public function canUpdateVmStatus($state="POWERED_ON"){
            return $this->power_state===$state;
        }


    }






?>


