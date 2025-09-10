<?php

class BillInfoMessage
{
  private $business;
  private $mysql;

  public function __construct()
  {
    $this->mysql = new Mysql();
  }

  public function getBusiness()
  {
    return $this->business;
  }

  public function findMessage($id)
  {
    $this->business = $this->findBusiness();
    $item = $this->query(["id" => $id])->getOne();
    return $this->setting($this->business, $item);
  }

  public function listMessages($filter = [])
  {
    $this->business = $this->findBusiness();
    $items = $this->query($filter)->getMany();
    foreach ($items as $i => $item) {
      $item = $this->setting($this->business, $item);
      $items[$i] = $item;
    }
    return $items;
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
    if (!$business)
      throw new Exception("No se encontró la empresa");
    if (!$business['whatsapp_key'])
      throw new Exception("No cuenta con una configuración de whatsapp");
    return $business;
  }

  private function setting($business, $item)
  {
    $baseUrl = BASE_URL;
    $businessName = $business['business_name'];
    $businessSymbol = $business['symbol'];
    $businessCode = $business['country_code'];
    $months = explode(",", $item['months']);
    $billIds = explode(",", $item['billIds']);
    $amount = format_money($item['remaining_amount']);
    $message = "Estimado cliente *{$item['client']}*, le recordamos que tiene una deuda *PENDIENTE por el monto TOTAL de {$businessSymbol}{$amount}*, correspondiente a los siguientes *MESES*:\n ";
    foreach ($months as $i => $date) {
      $datetime = DateTime::createFromFormat('Y-m-d', $date);
      $month = MONTHS[$datetime->format("m")] . "/" . $datetime->format("Y");
      $billId = encrypt($billIds[$i]);
      $link = "{$baseUrl}/invoice/document/{$billId}";
      $message .= "- {$month}: {$link}\n";
    }
    $message .= "Gracias por formar parte de nuestra familia *{$businessName}*, esperamos su pronto pago.\n";
    $message .= "Atte. *{$businessName}*";
    return [
      "number" => "{$businessCode}{$item['mobile']}",
      "body" => $message,
      "item" => $item
    ];
  }

  private function query($filters = [])
  {
    $query = $this->mysql->createQueryBuilder()
      ->from("clients cl")
      ->innerJoin("contracts c", "c.clientid = cl.id")
      ->innerJoin("document_type d", "cl.documentid = d.id")
      ->innerJoin("bills b", "b.clientid = cl.id AND b.state IN (2, 3)")
      ->where("c.state IN (2, 3, 4)")
      ->groupBy("c.id,c.internal_code,c.clientid,c.payday,c.days_grace,c.contract_date,c.suspension_date,c.finish_date,c.state,cl.names,cl.surnames,cl.document,d.document,cl.latitud,cl.longitud,cl.email,cl.mobile,cl.mobile_optional,cl.address,cl.reference")
      ->select("c.id,c.internal_code,c.clientid,c.payday,c.days_grace,c.contract_date,c.suspension_date,c.finish_date,c.state,CONCAT_WS(' ', cl.names, cl.surnames) AS client,cl.document,d.document AS name_doc,cl.latitud,cl.longitud,cl.email,cl.mobile,cl.mobile_optional,cl.address,cl.reference")
      ->addSelect("SUM(b.remaining_amount)", "remaining_amount")
      ->addSelect("GROUP_CONCAT(b.id)", "billIds")
      ->addSelect("GROUP_CONCAT(b.billed_month)", "months")
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
    return $query;
  }
}