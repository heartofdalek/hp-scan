<?php
namespace HPScan;

class HPScan {

    protected $hpscan_bin = '/usr/bin/hp-scan';
    protected $hpscan_args = array();
    protected $scan_from_duplex = false;
    protected $default_dst = '.';
    protected $scan_dst = '.';
    protected $default_type = 'png';
    protected $default_duplex_type = 'pdf';
    protected $scan_name = false;
    protected $scan_device = false;

    public function __construct($scan_device=null) {
        $this->setScanDevice($scan_device);
    }

    public function scan() {
        $return_filename = $this->buildAllArgs();
        
        $cmd = $this->hpscan_bin." ".implode(" ", array_values($this->hpscan_args))." 2>&1";
        
        $lastline = exec($cmd, $output, $return_var);
        
        $message = implode("\n", $output);
        
        if ($return_var>0 || preg_match('/error/i', $message)) {
            throw new \Exception($message, $return_var);
        }
        
        return $return_filename;
    }

    public function setScanDevice($scan_device=null) {
        if ($scan_device) {
            $this->scan_device = $scan_device;
        }
        
        return $this;
    }
    
    public function enableDuplexScan() {
        $this->scan_from_duplex = true;
        
        return $this;
    }
    
    public function disableDuplexScan() {
        $this->scan_from_duplex = false;
        
        return $this;
    }
    
    public function setScanDestinationDir($path) {
        if (!is_dir($path)) {
            throw new \Exception("Directory ".$path." is not directory");
        }
        
        if (!is_writeable($path)) {
            throw new \Exception("Directory ".$path." is not writeable");
        }

        $this->scan_dst = $path;
        
        return $this;
    }
    
    public function scanToFile($name) {
        if (!empty($name)) {
            $this->scan_name = $name;
        }
        
        return $this;
    }
    
    protected function buildAllArgs() {
        
        if ($this->scan_device) {
            $this->hpscan_args['device'] = "-d ".escapeshellarg($this->scan_device);
        }
        
        if ($this->scan_from_duplex) {
            $this->hpscan_args['duplex'] = "--duplex";
        }
        
        if (empty($this->scan_name)) {
            $extension = isset($this->hpscan_args['duplex']) ? $this->default_duplex_type : $this->default_type ;
            $this->scan_name = 'scan-'.str_replace(".", "", microtime(true)).'-'.getmypid().'.'.$extension;
        }
        
        $pathinfo = pathinfo($this->scan_name);
        
        if ( isset($pathinfo['dirname']) && $pathinfo['dirname']!=$this->default_dst ) {
            $this->setScanDestinationDir($pathinfo['dirname']);
        }
        
        $extension = isset($this->hpscan_args['duplex']) ? $this->default_duplex_type : $pathinfo['extension'];
        
        $final_filepath = $this->scan_dst.DIRECTORY_SEPARATOR.$pathinfo['filename'].'.'.$extension;
        
        $this->hpscan_args['file'] = '--file='.escapeshellarg($final_filepath);
        
        return $final_filepath;
    }
}

