<?php

class ClientSuspendMassiveService
{
  private Mysql $mysql;

  public function __construct()
  {
    $this->mysql = new Mysql("clients");
  }

  public function execute(string $date)
  {
    $business = $this->find_business();
    $collect = $this->list_clients($date);
    $result = [];
    // validate
    if (count($collect) == 0) {
      throw new Exception("No se encontró deudas");
    }
    // disabled
    foreach ($collect as $item) {
      $service = new ClientSuspendService($business);
      $service->execute($item['id']);
      array_push($result, $item);
    }
    // response
    return $result;
  }

  public function list_clients(string $date)
  {
    return $this->mysql->createQueryBuilder()
      ->from("clients", "cli")
      ->innerJoin("contracts c", "c.clientid = cli.id")
      ->innerJoin("bills b", "b.clientid = cli.id")
      ->where("DATE_ADD(b.expiration_date, INTERVAL c.days_grace DAY) <= '{$date}'")
      ->andWhere("b.state IN (2, 3)")
      ->andWhere("c.state = 2")
      ->andWhere("(b.promise_enabled = 0 or b.promise_date < '{$date}')")
      ->select("cli.*")
      ->getMany();
  }

  public function find_business()
  {
    return (Object) $this->mysql->createQueryBuilder()
      ->from("business")
      ->getOne();
  }
}