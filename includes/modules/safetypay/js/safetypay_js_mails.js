// Only for Examples
function showWindow(sEmailType)
{
	if (sEmailType == "EmailConfirmationDataForPeru")
	{
		var generator = window.open('','name1','left=200,top=00,width=720,height=860');

		generator.document.write('<div style="margin:0px; padding:10px; background-color:#D9E5F2; font-family:Arial, Helvetica, sans-serif; font-size:12px;">');
		generator.document.write('	<div style="background-color:#FFFFFF; margin-left:0px; padding-left:20px; font-family:Arial, Helvetica, sans-serif; font-size:13px;background-image:url(images/safetypay_logo.png); background-repeat:no-repeat; background-position: 98% 2%;"><br />Estimado(a) <span class="fieldsChange">Roberto</span>:<br />');
		generator.document.write('	<br />Gracias por comprar en <strong>Tienda ABC</strong> con SafetyPay.<br /><br />Usted ha terminado la <span style="color: #0000FF;font-weight: bold;">primera parte</span> del proceso de compra.<br /><br />Para <span style="color: #0000FF;font-weight: bold;">finalizar su compra</span>, <u>siga los siguientes pasos</u>:');
		generator.document.write('	<ol><li>Ingrese al servicio de banca en línea como lo hace habitualmente.<br /><a href="http://secure.saftpay.com/Prod_QAS/Express/BankSelector.aspx?TokenID=0108312466389594" target="_blank">Vea el listado de bancos donde completar su pago</a>.</li>');
		generator.document.write('		<li>En el BBVA vaya a <strong>Pago de Recibo</strong>s.<br />En el BCP vaya a <strong>Pago de Servicios</strong> y elija <strong>Empresas Diversas</strong>.</li><li>Elija a <strong>SAFETYPAY</strong>.</li><li>Ingrese el C&oacute;digo de Transacci&oacute;n: <strong>12345</strong></li><li>Ingrese el monto exacto de su compra en la moneda elegida: <strong>$ 9,999 USD, Dollars</strong>.</li>');
		generator.document.write('	</ol>');
		generator.document.write('	<p><span style="color: #0000FF;font-weight: bold;"><u>SOLO CUANDO UD. CONCLUYA ESTOS PASOS, SU PAGO ESTARÁ REALIZADO</u></span><br /><br />');
		generator.document.write('	<strong>Recuerde que tiene 02 horas para realizar el pago de esta compra o ésta caducará a las <span class="fieldsChange">12:15:08 del 24/10/2008</span>.</strong><br /><br />Gracias por confiar en nosotros para sus pagos en Internet.<br /><br />El Equipo SAFETYPAY</p>');
		generator.document.write('	<hr />');
		generator.document.write('	<p><span style="color: #0000FF;font-weight: bold;">DETALLE DE LA COMPRA A PAGAR</span></p>');
		generator.document.write('	<p><strong>C&oacute;digo de Transacci&oacute;n: </strong>12345<br /><strong>Monto a Pagar:</strong> $ 9,999<br /><strong>Moneda Seleccionada</strong>: USD, Dollars<br /><br /><strong>N&uacute;mero de Pedido</strong>: 9510<br /><strong>Comprador</strong>: John Flowers<br />');
		generator.document.write('	<br />Para completar su pago, debe ir a su banco en l&iacute;nea e ingresar los datos entregados.<br /></p><hr />');
		generator.document.write('	<p><span style="color: #0000FF;font-weight: bold;">DETALLES DEL COMERCIO</span></p>');
		generator.document.write('	<p><strong>Raz&oacute;n Social:</strong> Tienda ABC<br /><strong>Sitio Web:</strong> <a href="http://www.tiendaabc.com" target="_blank">http://www.tiendaabc.com</a><br /><strong>Tel&eacute;fono:</strong> (052)  12345-6789</p>');
		generator.document.write('	<p>Si tiene preguntas respecto a su compra, comun&iacute;quese a <a href="mailto:informes@tiendaabc.com">informes@tiendaabc.com</a>.</p><br />');	
		generator.document.write('	</div>');
		generator.document.write('</div>');
	}
	else if (sEmailType == "EmailConfirmationData")
	{
		var generator = window.open('','name2','left=100,top=50,width=390,height=300');
		
		generator.document.write('<div style="margin:0px; padding:10px; background-color:#D9E5F2; font-family:Arial, Helvetica, sans-serif; font-size:12px;">');
		generator.document.write('	<div style="color:#FFFFFF; background-color:#FFFFFF; font-size:18px; padding:5px; padding-top:10px; text-align:center"><a href="http:///www.safetypay.com" target="_blank"><img src="images/safetypay_logo.png" alt="SafetyPay Inc." border="0"></a></div>');
		generator.document.write('	<div style="padding-left:10px; padding-right:10px; background-color:#FFFFFF;text-align:justify"><br />Your order <strong>O-12345</strong> has been completed successfully.<br /><br />To complete payment of this transaction, please go to your <a href="http://secure.saftpay.com/Prod_QAS/Express/BankSelector.aspx?TokenID=0108323857966008" target="_blank">Online Banking</a> and use the following information:</div>');
		generator.document.write('	<div style="background-color:#FFFFFF; text-align:center; padding-top:5px; font-family:Arial, Helvetica, sans-serif; font-size:13px;"><strong>Transaction ID: 11610</strong></div>');
		generator.document.write('	<div style="background-color:#FFFFFF; text-align:center; padding-bottom:10px; font-family:Arial, Helvetica, sans-serif; font-size:13px;"><strong>Purchase Amount:&nbsp;&nbsp;75.00&nbsp;USD,&nbsp;</strong></div>');
		generator.document.write('	<div style="padding-left:10px; padding-right:10px; padding-bottom:10px; background-color:#FFFFFF;text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:12px;">You can <a href="https://secure.saftpay.com/prod%5Fqas/webservices/webservicestest/bank%5Ftest/" target="_blank">click here</a> to go directly to <span style="color:navy;">SafetyPay Test Bank</span>.</div>');
		generator.document.write('	<div style="padding-left:10px; padding-right:10px; padding-bottom:10px; background-color:#FFFFFF;text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:12px;"><strong>IMPORTANT:</strong> This Transaction will expire in 02 hours.<br /><br /><strong>Thank you for use SafetyPay</strong></div>');
		generator.document.write('</div>');
	}
	else if (sEmailType == "EmailConfirmPaidOrderForPeru")
	{
		var generator = window.open('','name3','left=100,top=50,width=680,height=550');
		
		generator.document.write('<div style="margin:0px; padding:10px; background-color:#D9E5F2; font-family:Arial, Helvetica, sans-serif; font-size:12px;">');
		generator.document.write('	<div style="background-color:#FFFFFF; margin-left:0px; padding-left:20px; font-family:Arial, Helvetica, sans-serif; font-size:13px;background-image:url(images/safetypay_logo.png); background-repeat:no-repeat; background-position: 98% 2%;"><br />Estimado(a) <span class="fieldsChange">Roberto</span>:<br /><br />');
		generator.document.write('	<p>Se ha  recibido el pago de su transacci&oacute;n SAFETYPAY, confirmando el pago del pedido.<br /><br />Gracias por confiar en nosotros para sus pagos en Internet.<br /><br />El Equipo SAFETYPAY.</p>');
		generator.document.write('	<hr />');
		generator.document.write('	<p><span style="color: #0000FF;font-weight: bold;">DETALLE DE LA COMPRA A PAGAR</span></p>');
		generator.document.write('	<p><strong>C&oacute;digo de Transacci&oacute;n: </strong>12345<br /><strong>Monto a Pagar:</strong> $ 9,999<br /><strong>Moneda Seleccionada</strong>: USD, Dollars<br /><br /><strong>N&uacute;mero de Pedido</strong>: 9510<br /><strong>Comprador</strong>: John Flowers<br />');
		generator.document.write('	<br />Para completar su pago, debe ir a su banco en l&iacute;nea e ingresar los datos entregados.<br /></p><hr />');
		generator.document.write('	<p><span style="color: #0000FF;font-weight: bold;">DETALLES DEL COMERCIO</span></p>');
		generator.document.write('	<p><strong>Raz&oacute;n Social:</strong> Tienda ABC<br /><strong>Sitio Web:</strong> <a href="http://www.tiendaabc.com" target="_blank">http://www.tiendaabc.com</a><br /><strong>Tel&eacute;fono:</strong> (052)  12345-6789</p>');
		generator.document.write('	<p>Si tiene preguntas respecto a su compra, comun&iacute;quese a <a href="mailto:informes@tiendaabc.com">informes@tiendaabc.com</a>.</p><br />');	
		generator.document.write('	</div>');
		generator.document.write('</div>');
	}
	else if (sEmailType == "EmailConfirmPaidOrder")
	{
		var generator = window.open('','name4','left=100,top=50,width=390,height=200');
		
		generator.document.write('<div style="margin:0px; padding:10px; background-color:#D9E5F2; font-family:Arial, Helvetica, sans-serif; font-size:12px;">');
		generator.document.write('	<div style="color:#FFFFFF; background-color:#FFFFFF; font-size:18px; padding:5px; padding-top:10px; text-align:center"><a href="http:///www.safetypay.com" target="_blank"><img src="images/safetypay_logo.png" alt="SafetyPay Inc." border="0"></a></div>');
		generator.document.write('	<div style="padding-left:10px; padding-right:10px; background-color:#FFFFFF;text-align:justify"><br />Se ha  recibido el pago de su Transacci&oacute;n SAFETYPAY <strong>11610</strong>, confirmando el pago del pedido <strong>O-12345</strong>.<br /><br /><br /></div>');
		generator.document.write('	<div style="padding-left:10px; padding-right:10px; padding-bottom:10px; background-color:#FFFFFF;text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:12px;"><strong>Thank you for use SafetyPay</strong></div>');
		generator.document.write('</div>');
	}
	generator.document.focus();
}
