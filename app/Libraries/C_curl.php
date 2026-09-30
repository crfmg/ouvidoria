<?php
namespace App\Libraries;
class C_curl {
    public function email_origem_externa(array $campos): string {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);
        curl_setopt($curl, CURLOPT_URL, "http://api.crfmg.org.br/informatica/disparo_email/email.php");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $campos);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        unset($curl);
        return (string)$response;
    }
    public function email_origem_interna(array $campos): string {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);
        curl_setopt($curl, CURLOPT_URL, "http://192.168.0.103/informatica/disparo_email/email.php");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $campos);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        unset($curl);
        return (string)$response;
    }
    public function __destruct() {}
}
/*
array(
    'conta' => '',//nome da conta que fará o disparo do email. ex: noreply
    'email' => '',//destinatário que irá receber o email. ex: ti@crfmg.org.br
    'ad_user' => '',//login no AD (disparo manual) ou nome do servidor (disparo automático). ex: CRFMG-SRV12
    'sistema' => '',//nome do sistema de origem da solicitação de disparo. ex: Comunicado de Ausência
    'assunto' => '',//título do email. ex: Aviso de Ausências Excedidas
    'conteudo' => '',//corpo do email. ex: A PF 12345 excedeu os 30 dias de ausência na PJ 12345
    'funcionalidade' => ''//nome da parte do sistema que solicitou o disparo. ex: Limite de Ausência Excedido
);
*/
?>