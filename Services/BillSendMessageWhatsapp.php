<?php

class BillSendMessageWhatsapp
{
  private $mysql;

  public function __construct()
  {
    $this->mysql = new Mysql();
  }

  public function send($filters = [])
  {
    try {
      $array_success = [];
      $array_error = [];
      $data = $this->listBills($filters);
      $business = $this->findBusiness();
      // iter
      foreach ($data as $item) {
        $client = (Object) $item;

        $message = new PlantillaWspInfoService(
          $client,
          $business
        );

        $message->setMysql($this->mysql);
        $billIds = [...$client->billIds];
        $message->setArrayBillId($billIds);
        $str_message = $message->execute("PAYMENT_PENDING");

        $whatsapp = new SendWhatsapp($business);
        $response = $whatsapp->send($client->mobile, $str_message);

        if ($response) {
          array_push($array_success, $client->mobile);
        } else {
          array_push($array_error, $client->mobile);
        }
      }
      // response
      return [
        "message" => "Mensajes enviados",
        "data" => $array_success,
        "error" => $array_error
      ];
    } catch (\Throwable $th) {
      return [
        "message" => $th->getMessage()
      ];
    }
  }

  private function listBills($filters = [])
  {
    $query = $this->mysql->createQueryBuilder()
      ->from("clients cl")
      ->innerJoin("contracts c", "c.clientid = cl.id")
      ->innerJoin("document_type d", "cl.documentid = d.id")
      ->innerJoin("bills b", "b.clientid = cl.id AND b.state IN (2, 3)")
      ->where("c.state IN (2, 3, 4)")
      ->groupBy("cl.id, cl.names, cl.surnames, cl.documentid, cl.document, cl.mobile, cl.mobile_optional, cl.email, cl.address")
      ->addGroupBy("cl.reference")
      ->addGroupBy("cl.note")
      ->addGroupBy("cl.latitud")
      ->addGroupBy("cl.longitud")
      ->addGroupBy("cl.state")
      ->addGroupBy("cl.net_router")
      ->addGroupBy("cl.net_name")
      ->addGroupBy("cl.net_password")
      ->addGroupBy("cl.net_localaddress")
      ->addGroupBy("cl.net_ip")
      ->select("cl.id, cl.names, cl.surnames, cl.documentid, cl.document, cl.mobile, cl.mobile_optional, cl.email, cl.address")
      ->addSelect("cl.reference", "reference")
      ->addSelect("cl.note", "note")
      ->addSelect("cl.latitud", "latitud")
      ->addSelect("cl.longitud", "longitud")
      ->addSelect("cl.state", "state")
      ->addSelect("cl.net_router", "net_router")
      ->addSelect("cl.net_name", "net_name")
      ->addSelect("cl.net_password", "net_password")
      ->addSelect("cl.net_localaddress", "net_localaddress")
      ->addSelect("cl.net_ip", "net_ip")
      ->addSelect("GROUP_CONCAT(b.id)", "billIds")
      ->addSelect("COUNT(b.id)", "counter");
    // filters
    if (isset($filters['id'])) {
      $condicion = $filters['id'];
      $query->andWhere("cl.id = '{$condicion}'");
    }
    if (isset($filters['phone:required'])) {
      $condicion = $filters['phone:required'];
      $query->andWhere("(cl.mobile is not null OR cl.mobile <> '')");
    }
    if (isset($filters['deuda'])) {
      $condicion = $filters['deuda'];
      $query->andHaving("counter > {$condicion}");
    }
    if (isset($filters['payday'])) {
      $condicion = $filters['payday'];
      $query->andWhere("c.payday = '{$condicion}'");
    }
    // response
    $data = $query->getMany();

    foreach ($data as $key => $item) {
      $data[$key]['billIds'] = explode(",", $item['billIds']);
    }

    return $data;
  }

  private function findBusiness()
  {
    $business = $this->mysql
      ->createQueryBuilder()
      ->from("business b")
      ->innerJoin("currency c", "c.id = b.currencyid")
      ->setLimit(1)
      ->select("b.*, c.symbol")
      ->getOne();
    if (!$business) {
      throw new Exception("No se encontró la empresa");
    }

    if (!$business['whatsapp_key']) {
      throw new Exception("No cuenta con una configuración de whatsapp");
    }

    return (Object) $business;
  }
}