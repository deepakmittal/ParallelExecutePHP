<?php

/**
 * Runs multiple shell commands parallelly and wait for each one of them to execute
 */
class ParallelExecutor{
    private static $LOCAL_TEMPORARY_FOLDER = "/tmp";
    private $jobsGroupId;
    private $jobs = array();
    private $commands = array();
    private $isVerbose = false;
    private $maxParallelJobs = 10;
    
    static function getLOCAL_TEMPORARY_FOLDER() {
        return self::$LOCAL_TEMPORARY_FOLDER;
    }
    
    function getMaxParallelJobs() {
        return $this->maxParallelJobs;
    }

    function setMaxParallelJobs($maxParallelJobs) {
        $this->maxParallelJobs = $maxParallelJobs;
    }

    function __construct() {
            $this->jobsGroupId = $this->generateRandomString();
            
    }    
    
    /**
     * Specify local temporary foldar to keep temporary data
     * @param type $LOCAL_TEMPORARY_FOLDER
     */
    public static function setLOCAL_TEMPORARY_FOLDER($LOCAL_TEMPORARY_FOLDER) {
        self::$LOCAL_TEMPORARY_FOLDER = $LOCAL_TEMPORARY_FOLDER;
    }

    /**
     * Set verbose mode
     * @param type $isVerbose
     */
    function setVerbose($isVerbose=true) {
        $this->isVerbose = $isVerbose;
    }
        
    public function addCommand($cmd){
        $this->commands[] = $cmd;
    }
    public function addCommands($cmds){
        $this->commands = array_merge($this->commands, $cmds);
    }
    public function run(){
        $this->show("beginning execution of commands. jobs group id: {$this->jobsGroupId}.....");
        $this->makeDir(self::$LOCAL_TEMPORARY_FOLDER . "/phpParallelExecute");
        foreach($this->commands as $cmd){
            $this->runCommand($cmd);
        }
        $this->show("all commands submitted for execution.\n");
    }
    public function runAndWait(){
        $this->run();
        $this->wait();
    }
    public function wait(){
        foreach($this->jobs as &$job){
            $this->show("---------------------------------------------------------------------------------");
            $this->show("waiting for job {$job['job_id']}.......");
            $this->waitForJob($job['process_id']);
            $this->show("SUCCESS");
            $job['output'] = file_get_contents($job['output_file']);
            $this->show("output:\n". $job['output']  );
            $this->show("---------------------------------------------------------------------------------");
            unlink($job['output_file']);
            //rmdir($job['dir']);
            unset($job['output_file']);
            unset($job['dir']);
        }
        rmdir(self::$LOCAL_TEMPORARY_FOLDER . "/phpParallelExecute/{$this->jobsGroupId}");
        $this->show("\n\nall jobs executed successfully.\n");
    }
    public function getParallelRunningCount(){
        $count = 0;
        foreach($this->jobs as $job){
            if($this->isProcessRunning($job['process_id'])){
                $count ++;
            }
        }
        return $count;
    }
    public function isRunning(){
        foreach($this->jobs as $job){
            if($this->isProcessRunning($job['process_id'])){
                return true;
            }
        }
        return false;
    }
    public function getDetails(){
        return $this->jobs;
    }
    private function show($str){
        if($this->isVerbose){
            echo "\n $str";
        }
    }   
    private function runCommand($cmd){
        $waitingTime = 0;
        while($this->getParallelRunningCount() >= $this->maxParallelJobs){
            $this->show("waiting for slot. {$this->maxParallelJobs} jobs already active." ) ;
            sleep(20);
            $waitingTime += 20;
        }
        $this->show("command: $cmd");
        $jobId = $this->generateRandomString();
        $path = self::$LOCAL_TEMPORARY_FOLDER . "/phpParallelExecute";
        $this->makeDir($path);
        $path .= "/{$this->jobsGroupId}"; 
        $this->makeDir($path);
        $outputfile = "$path/$jobId.out";
        $pidfile = "$path/$jobId.pid";
        exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
        $processIdDetails = file_get_contents($pidfile);
        unlink($pidfile);
        $processIdArr = explode("\r",$processIdDetails) ;
        $processId = $processIdArr[0];
        $this->jobs[] = array(
            "job_id" => $jobId,
            "command" => $cmd,
            "process_id" => $processId,
            "output_file" => $outputfile,
            "dir"   => $path,
            "status"    => "running"
        );
        $this->show("   job id: $jobId, process id: $processId");
    }
    private function waitForJob($pid){
        while(1){
                if(!$this->isRunning($pid)){
                        return;
                }
                sleep(2);
        }
    }
    private function isProcessRunning($pid){
        try{
            $result = shell_exec(sprintf("ps %d", $pid));
            if( count(preg_split("/\n/", $result)) > 2){
                return true;
            }
        }catch(Exception $e){
            
        }
            return false;
     }
     
     private function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    private function makeDir($path){
        if(!is_dir($path)){
            mkdir($path);
        }
    }
}
