<?php
/**
 * Devbr\Cli\Key
 * PHP version 7
 *
 * @category  Tools
 * @package   Cli
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://dbrasil.tk/devbr
 */

namespace Devbr\Cli;

use Devbr;

/**
 * Devbr\Cli\Key Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://dbrasil.tk/devbr
 */
class Key
{
    private $cmd            = null;
    private $arg            = null;
    private $configKeyPath  = null;

    /**
     * Constructor
     * @param string $cmd command
     * @param array $arg others command line args
     */
    function __construct(
        $cmd = null,
        $arg = null
    ) {
    
        $this->cmd = strtolower($cmd);
        $this->arg = $arg;
        $this->configKeyPath = Main::getConfigDir().'/Devbr/Key/';
    }

    /**
     * Run command
     *
     * @return void return from MAIN CLTool
     */
    function run()
    {
        switch ($this->cmd) {
            case 'generate':
                return $this->cmdGenerate();
                break;

            case 'list':
                return $this->cmdList();
                break;

            default:
                echo "\n\n  Command \"key:".$this->cmd."\" not exists!";
                exit(Main::help());
                break;
        }
        return;
    }

    /**
     * Command Generate
     *
     * @return string display results
     */
    private function cmdGenerate()
    {
        //check if path exists
        if (!is_dir($this->configKeyPath)) {
            Main::copyDirectoryContents(dirname(__DIR__).'/Config/Devbr/Key', $this->configKeyPath);
        }
        //Now, OPEN_SSL
        $this->createKeys();
        return "\n  Can, OpenSSL keys & certificates - created success!".
               "\n  Location: ".$this->configKeyPath."\n\n";
    }

    /**
     * Command LIST
     *
     * @return string display results
     */
    private function cmdList()
    {
        


        $a = array_diff(openssl_get_md_methods(true), openssl_get_md_methods());

        $b = array_diff(openssl_get_cipher_methods(true), openssl_get_cipher_methods());
        $b = array_filter($b,function($c) { return stripos($c,"des")===FALSE; } );
        $b = array_filter($b,function($c) { return stripos($c,"rc2")===FALSE; } );

        sort($a);
        sort($b);

        if(count($a) > count($b)){
            $x = $a;
            $y = $b;
        } else {
            $x = $b;
            $y = $a;
        }

        $o = "\n\n\t".str_pad('Ciphers', 30, ' ', STR_PAD_RIGHT)."|  Modes\n";
        $o .= "\t".str_pad('-------', 30, '-', STR_PAD_RIGHT)."|-----------------\n";

        for($i = 0; count($x) > $i; $i ++) {
            $o .= "\t".str_pad($x[$i],30,' ',STR_PAD_RIGHT)."|  ".
                  (isset($y[$i])?$y[$i]:'-')."\n";
        }
        return $o."\n\n";
    }

    /**
     * Create Can anda SSL keys
     *
     * @return void none
     */
    private function createKeys()
    {
        //Create Can Keys
        shuffle(Devbr\Can::$base);
        shuffle(Devbr\Can::$extra_base);
        file_put_contents($this->configKeyPath.'can.key', implode(Devbr\Can::$base)."\n".implode(Devbr\Can::$extra_base));

        $SSLcnf = [];
        $dn = [];

        //get configurations
        include $this->configKeyPath.'openssl.config.php';

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new($SSLcnf);

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, $SSLcnf);

        // You will usually want to create a self-signed certificate at this
        // point until your CA fulfills your request.
        // This creates a self-signed cert that is valid for 365 days
        $sscert = openssl_csr_sign($csr, null, $privkey, 365, $SSLcnf);

        //CERTIFICADO
        openssl_csr_export_to_file($csr, $this->configKeyPath.'certificate.crt', false);

        //CERTIFICADO AUTO-ASSINADO
        openssl_x509_export_to_file($sscert, $this->configKeyPath.'self_signed_certificate.cer', false);

        //CHAVE PRIVADA (private.pem)
        openssl_pkey_export_to_file($privkey, $this->configKeyPath.'private.key', null, $SSLcnf);

        //CHAVE PÚBLICA (public.key)
        file_put_contents($this->configKeyPath.'public.key', openssl_pkey_get_details($privkey)['key']);
    }
}
