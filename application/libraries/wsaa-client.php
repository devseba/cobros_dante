<?php //echo "llega";
# Author: Gerardo Fisanotti - DvSHyS/DiOPIN/AFIP - 13-apr-07
# Function: Get an authorization ticket (TA) from AFIP WSAA
# Input:
#        WSDL, CERT, PRIVATEKEY, PASSPHRASE, SERVICE, URL
#        Check below for its definitions
# Output:
#        TA.xml: the authorization ticket as granted by WSAA.
#==============================================================================
define ("URLBASE", "application/libraries/webservice/ta/"); 


define ("WSDL", URLBASE."wsaa.wsdl");     # The WSDL corresponding to WSAA
define ("CERT", URLBASE."certificadoDante.crt");       # The X.509 certificate in PEM format
define ("PRIVATEKEY", URLBASE."ClavePrivada.key"); # The private key correspoding to CERT (PEM)
define ("PASSPHRASE", ""); # The passphrase (if any) to sign
define ("PROXY_HOST", ""); # Proxy IP, to reach the Internet
define ("PROXY_PORT", "");            # Proxy TCP port
define ("URL", "https://wsaa.afip.gov.ar/ws/services/LoginCms");




#define ("URL", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
#------------------------------------------------------------------------------
# You shouldn't have to change anything below this line!!!
#==============================================================================
function CreateTRA($SERVICE)
{
  $TRA = new SimpleXMLElement(
    '<?xml version="1.0" encoding="UTF-8"?>' .
    '<loginTicketRequest version="1.0">'.
    '</loginTicketRequest>');
  $TRA->addChild('header');
  $TRA->header->addChild('uniqueId',date('U'));
  $TRA->header->addChild('generationTime',date('c',date('U')-60));
  $TRA->header->addChild('expirationTime',date('c',date('U')+60));
  $TRA->addChild('service',$SERVICE);
  $TRA->asXML(URLBASE.'TRA.xml');
}
#==============================================================================
# This functions makes the PKCS#7 signature using TRA as input file, CERT and
# PRIVATEKEY to sign. Generates an intermediate file and finally trims the 
# MIME heading leaving the final CMS required by WSAA.
function SignTRA()
{
  $STATUS=openssl_pkcs7_sign(URLBASE."TRA.xml", URLBASE."TRA.tmp", "file://".CERT,
    array("file://".PRIVATEKEY, PASSPHRASE),
    array(),
    !PKCS7_DETACHED
    );
  if (!$STATUS) {exit("ERROR generating PKCS#7 signature\n");}
  $inf=fopen(URLBASE."TRA.tmp", "r");
  $i=0;
  $CMS="";
  while (!feof($inf)) 
    { 
      $buffer=fgets($inf);
      if ( $i++ >= 4 ) {$CMS.=$buffer;}
    }
  fclose($inf);
#  unlink("TRA.xml");
  unlink(URLBASE."TRA.tmp");
  return $CMS;
}
#==============================================================================
function CallWSAA($CMS)
{
  $client=new SoapClient(WSDL, array(
          //'proxy_host'     => PROXY_HOST,
          //'proxy_port'     => PROXY_PORT,
          'soap_version'   => SOAP_1_2,
          'location'       => URL,
          'trace'          => 1,
          'exceptions'     => 0
          )); 
  $results=$client->loginCms(array('in0'=>$CMS));
  file_put_contents(URLBASE."request-loginCms.xml",$client->__getLastRequest());
  file_put_contents(URLBASE."response-loginCms.xml",$client->__getLastResponse());
  if (is_soap_fault($results)) 
    {exit("SOAP Fault: ".$results->faultcode."\n".$results->faultstring."\n");}
  return $results->loginCmsReturn;
}
#==============================================================================
function ShowUsage($MyPath)
{
  printf("Uso  : %s Arg#1 Arg#2\n", $MyPath);
  printf("donde: Arg#1 debe ser el service name del WS de negocio.\n");
  printf("  Ej.: %s wsfe\n", $MyPath);
}
#==============================================================================


function get_TA()
{

ini_set("soap.wsdl_cache_enabled", "0");
if (!file_exists(CERT)) {exit("Failed to open ".CERT."\n");}
if (!file_exists(PRIVATEKEY)) {exit("Failed to open ".PRIVATEKEY."\n");}
if (!file_exists(WSDL)) {exit("Failed to open ".WSDL."\n");}

//$SERVICE='wsmtxca';
$SERVICE='wsfe';

//echo "TRA:... \n";
CreateTRA($SERVICE);
//echo "fin TRA \n";
$CMS=SignTRA();
//var_dump($CMS); 
//echo "fin SingTRA \n";
$TA=CallWSAA($CMS);
//echo "TA \n";
if (!file_put_contents(URLBASE."TA.xml", $TA)) {exit();}
//echo "FIN \n";

}

//get_TA();




?>
