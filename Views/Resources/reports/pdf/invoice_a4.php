<?php
	$business = $data['business'];
	$bill = $data['bill'];
	$detail = $data['detail'];
	$payments = $data['payments'];
	$correlative = str_pad($bill['correlative'],7,"0", STR_PAD_LEFT);
	$logo = 'Assets/uploads/business/'.$business['logotyope'];
	$comprobante = $bill['serie'] . '|' . $correlative;
	$name_qr = $business['ruc']. '|' . $bill['voucher'] . '|' . $comprobante . '|0.00|' . $bill["total"] . '|' . $bill['date_issue'] . '|2|' . $bill['document'] . '|';
	if($bill['state'] == 1){
		$state = "PAGADO";
	}else if($bill['state'] == 2){
		$state = "PENDIENTE";
	}else if($bill['state'] == 3){
		$state = "VENCIDO";
	}else if($bill['state'] == 4){
		$state = "ANULADO";
	}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="<?= base_style() ?>/css/style_a4.css">
        <title><?= $bill['serie'].'-'.$correlative ?></title>
    </head>
   <body>
        <table class="cabecera1">
            <tr>
                <th>
                    <?php if(file_exists($logo)): ?>
                        <img src="<?= base_url()."/".$logo ?>" width="270px">
                    <?php else: ?>
                        <img src="<?= base_style().'/images/logotypes/superwisp.png' ?>" width="270px">
                    <?php endif; ?>
                </th>
            </tr>
            <tr>
                <th><span style="font-size:12px;"><?= $business['slogan'] ?></span></th>
            </tr>
            <tr>
                                <?php
										$mobiles_business = '';
										if(!empty($business['mobile'])){
											$mobiles_business .= $business['mobile'];
										}
										if(!empty($business['mobile_refrence'])){
											$mobiles_business .= ' - ';
											$mobiles_business .= $business['mobile_refrence'];
										}
								 ?>
								<th>Cel. <?= $mobiles_business ?> </th>
            </tr>
            <tr>
                <th><?= $business['address'] ?></th>
            </tr>
        </table><br>
        <table class="cabecera2">
            <tr>
                <td>RUC <?= $business['ruc'] ?></td>
            </tr>
            <tr>
                <td><?= $bill['voucher'] ?></td>
            </tr>
            <tr>
                <td><?= $bill['serie']."-".$correlative ?></td>
            </tr>
        </table>
        <table border="0">
            <tr>
                <td>
                    <table class="cabecera3">
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th width="25%">CLIENTE</th>
                            <td>:&nbsp;&nbsp;<?= $bill['names']." ".$bill['surnames'] ?></td>
                        </tr>
                        <tr>
                          <th><?= $bill['type_doc'] ?></th>
                          <td>:&nbsp;&nbsp;<?= $bill['document'] ?></td>
                        </tr>
                        <tr>
                          <th>CELULAR</th>
                          <td>:&nbsp;&nbsp;<?= $bill['mobile'] ?></td>
                        </tr>
                        <tr>
                          <th>DIRECCIÓN</th>
                          <td>:&nbsp;&nbsp;<?= $bill['address'] ?></td>
                        </tr>
                        <tr>
                          <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="cabecera3">
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <th>FECHA EMISIÓN</th>
                            <td>:&nbsp;&nbsp;<?= date("d/m/Y",strtotime($bill['date_issue'])) ?></td>
                        </tr>
                        <tr>
                            <th>FECHA DE VENC.</th>
                            <td>:&nbsp;&nbsp;<?= date("d/m/Y",strtotime($bill['expiration_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>MONEDA</th>
                            <td>:&nbsp;&nbsp;<?= $business['money_plural'] ?></td>
                        </tr>
												<tr>
														<th>ESTADO</th>
														<td>:&nbsp;&nbsp;<?= $state ?></td>
												</tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="cabecera5">
            <thead class="encabeza">
                <tr>
                    <th align="center">CANT.</th>
                    <th align="left">DESCRIPCION</th>
                    <th align="center">P/U</th>
                    <th align="center">IMPORTE</th>
                </tr>
            </thead>
          <tbody class="zebra">
              <?php if(!empty($detail)): ?>
              <?php foreach ($detail AS $row) : ?>
                      <tr>
                          <td align="center"><?= $row["quantity"]?></td>
                          <td align="left"><?= $row["description"] ?></td>
                          <td align="center"><?= format_money($row["price"])?></td>
                          <td align="center"><?= format_money($row["total"])?></td>
                      </tr>
              <?php endforeach ?>
              <?php else: ?>
                      <tr>
                          <td class="text-center" colspan="4">NO HAY REGISTROS.</td>
                      </tr>
              <?php endif; ?>
              </tbody>
                <tr>
                    <th align="right" colspan="3">SUBTOTAL</th>
                    <th align="center"><?= $business['symbol'].format_money($bill["subtotal"]) ?></th>
                </tr>
                <tr>
                    <th align="right" colspan="3">DESCUENTO</th>
                    <th align="center"><?= $business['symbol'].format_money($bill["discount"]) ?></th>
                </tr>
                <tr>
                    <th align="right" colspan="3">TOTAL</th>
                    <th align="center"><?= $business['symbol'].format_money($bill["total"]) ?></th>
                </tr>
        </table>
        <table class="cabecera6" style="margin-bottom:7px;">
          <tr>
            <td><span>IMPORTE EN LETRAS:</span> <?= numbers_letters($bill['total'],$business['money'],$business['money_plural']) ?></td>
          </tr>
        </table>
        <table border="0">
						<tr>
							<td>
                <?php if(isset($bill['promise_date'])){?>
                  <hr class="hr">
                  <p style="font-size:9px;">
                    <span style="font-size:11px;">
                      <span style="font-family:arial,helvetica,sans-serif;">
                        <strong>FECHA COMPROMISO: </strong> <?= date_format(date_create($bill['promise_date']), "Y/m/d") ?>
                      </span>
                    </span>
                  </p>
                <?php }?>
                <table class="cabecera3">
                  <?php if(!empty($payments)){ ?>
                  <tr>
                    <td> <strong>PAGOS</strong></td>
                  </tr>
                  <?php foreach ($payments as $payment) : ?>
                    <tr><td><?= $payment['payment_type'] ?> - <?= date("d/m/Y H:i",strtotime($payment['payment_date'])) ?> - <?= $business['symbol'].format_money($payment['amount_paid']) ?></td></tr>
                  <?php endforeach ?>
                  <?php } ?>
                  <tr><?= $business['footer_text'] ?></tr>
                </table>
							</td>
							<?php if($bill['voucherid'] == 2 || $bill['voucherid'] == 3): ?>
							<td width="30%">
							  	<table class="cabecera3">
							    	<tr>
							     	 <td align="center"><img src="<?= generate_qr($name_qr,5,"H",3); ?>" width="135px"></td>
							    	</tr>
							  	</table>
							</td>
							<?php endif; ?>
						</tr>
				</table>
    </body>
</html>
