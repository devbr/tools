<?php
/**
 * Lib\Cli\Key
 * PHP version 7
 *
 * @category  Tools
 * @package   Cli
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://paulorocha.tk/devbr
 */

namespace Lib\Cli;

use Lib;

/**
 * Lib\Cli\Key Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
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
        $this->configKeyPath = (defined('_CONFIG') ? _CONFIG : dirname(__DIR__, 4).'/Config/').'Key/';
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
            Main::checkAndOrCreateDir($this->configKeyPath, true);
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
        $o = "\n\n  Ciphers:";
        foreach (mcrypt_list_algorithms() as $x) {
            $o .= "\n\t".$x;
        }
        $o .= "\n\n  Cipher Modes:";
        foreach (mcrypt_list_modes() as $x) {
            $o .= "\n\t".$x;
        }
        return $o;
    }

    /**
     * Create Can anda SSL keys
     *
     * @return void none
     */
    private function createKeys()
    {
        //Create Can Keys
        shuffle(Lib\Can::$base);
        shuffle(Lib\Can::$extra_base);
        file_put_contents($this->configKeyPath.'can.key', implode(Lib\Can::$base)."\n".implode(Lib\Can::$extra_base));

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
