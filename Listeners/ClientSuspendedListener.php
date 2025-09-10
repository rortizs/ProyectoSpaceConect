<?php

class ClientSuspendedListener implements Observer
{

  private Mysql $mysql;
  private object $businnes;

  public function __construct(Mysql $mysql, object $businnes)
  {
    $this->mysql = $mysql;
    $this->businnes = $businnes;
  }

  public function update($client)
  {
    if (!$client->mobile) {
      return;
    }
    // enviar mensaje
    $message = new PlantillaWspInfoService($client, $this->businnes);
    $str_message = $message->execute("CLIENT_SUSPENDED");
    $wsp = new SendWhatsapp($this->businnes);
    $mobile = "{$this->businnes->country_code}{$client->mobile}";
    $wsp->send($mobile, $str_message);
  }
}