<?php
  include 'config/functions.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Formulario Desacoplado</title>
  <link rel="stylesheet" href="<?php echo VISA_URL_CSS ?>">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.18.0/css/mdb.min.css" rel="stylesheet">
  <style>
    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
      color: #999999 !important;
      opacity: 1; /* Firefox */
    }

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
      color: #999999 !important;
    }

    ::-ms-input-placeholder { /* Microsoft Edge */
      color: #999999 !important;
    }
  </style>
</head>

<body>

  <br>

  <div class="container">
    <h1 class="text-center">Formulario Desacoplado</h1>
    <hr>

    <p id="loading">Cargando</p><br>

    <div class="row justify-content-md-center">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Formulario de pago</h4>
            <div class="row">
              <div class="col-12">
                <div id="txtNumeroTarjeta" class="form-control form-control-sm ncp-card"></div>
                <small id="msjNroTarjeta" class="form-text text-muted red-text m-0"></small>
              </div>
            </div>
            <br>

            <div class="row mt-0">
              <div class="col-6">
                <div id="txtFechaVencimiento" class="form-control form-control-sm"></div>
                <small id="msjFechaVencimiento" class="form-text text-muted red-text m-0"></small>
              </div>
              <div class="col-6">
                <div id="txtCvv" class="form-control form-control-sm"></div>
                <small id="msjCvv" class="form-text text-muted red-text m-0"></small>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-6">
                <input type="text" id="nombre" class="form-control form-control-sm" placeholder="Nombre">
              </div>
              <div class="col-6">
                <input type="text" id="apellido" class="form-control form-control-sm" placeholder="Apellido">
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <input type="text" id="email" class="form-control form-control-sm" placeholder="Email">
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-md-12" id="cuotas" style="display: none;">
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <button class="btn btn-primary btn-block" onclick="pay()" id="btnProcesar"></button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>


  <script src="<?php echo VISA_URL_JS ?>"></script>

  <script src="assets/js/jquery.min.js"></script>
  <script>

  loadPage();

    function loadPage() {
      $("#loading").show();
      window.channel = prompt("Ingrese el canal\nPago único: web\nTokenización: paycard", "");
      if (window.channel == "paycard") {
        window.amount = 1;
        document.getElementById("btnProcesar").innerHTML = "Agregar tarjeta";
      } else if (window.channel == "web") {
        window.amount = prompt("Ingrese el importe a pagar", "");
        document.getElementById("btnProcesar").innerHTML = "Pagar ";
      } else {
        alert("Channel no válido, recargar la página");
        return false;
      }
      loadForm();
    }

    function loadForm() {
      console.log("Cargando formulario");
      $.get('api/sesion.php?amount='+window.amount+'&channel='+window.channel, function(response) {
        console.log('Response: ', response);
        window.configuration = {
          sessionkey: String(response['sesionKey']),
          channel: String(response['channel']),
          merchantid: String(response['merchantId']),
          purchasenumber: String(response['purchaseNumber']),
          amount: String(response['amount']),
          callbackurl: '',
          language: "es",
          font: "https://fonts.googleapis.com/css?family=Montserrat:400&display=swap",
        };

        window.purchase = String(response['purchaseNumber']);
        window.dcc = false;

        window.payform.setConfiguration(window.configuration);

        var elementStyles = {
          base: {
            color: 'black',
            margin: '0',
            // width: '100% !important',
            // fontWeight: 700,
            fontFamily: "'Montserrat', sans-serif",
            // fontSize: '16px',
            fontSmoothing: 'antialiased',
            placeholder: {
              color: '#999999'
            },
            autofill: {
              color: '#e39f48',
            }
          },
          invalid: {
            color: '#E25950',
            '::placeholder': {
              color: '#FFCCA5',
            }
          }
        };

        // Número de tarjeta
        window.cardNumber = window.payform.createElement(
          'card-number', {
            style: elementStyles,
            placeholder: 'Número de Tarjeta'
          },
          'txtNumeroTarjeta'
        );

        window.cardNumber.then(element => {

          // 6 primeros dígitos
          element.on('bin', function(data) {
            console.log('BIN: ', data);
          });
          
          // 4 últimos dígitos
          element.on('lastFourDigits', function(data) {
            console.log('last4digits: ', data);
          });

          element.on('dcc', function(data) {
            console.log('DCC', data);
            if (data != null) {
              var response = confirm("Usted tiene la opción de pagar su factura en: PEN " + window.amount + " o " + data['currencyCodeAlpha'] + " " + data['amount'] + ". Una vez haya hecho su elección, la transacción continuará con la moneda seleccionada. Tasa de cambio PEN a " + data['currencyCodeAlpha'] +": " + data['exchangeRate'] + " \n \n" + data['currencyCodeAlpha'] + " " +data['amount'] + "\nPEN = " + data['currencyCodeAlpha'] + " " + data['exchangeRate'] + "\nMARGEN FX: " + data['markup']);
              if (response == true) {
                window.dcc = true;
              } else {
                window.dcc = false;
              }
            }
          });

          element.on('installments', function(data) {
            console.log('INSTALLMENTS: ', data);
            if (data != null && window.channel == "web") {
              window.credito = true;
              var cuotas = document.getElementById('cuotas');
              cuotas.style.display = "block";

              var select = document.createElement('select');
              select.setAttribute("class", "form-control form-control-sm mb-4");
              select.setAttribute("id", "selectCuotas");
              optionDefault = document.createElement('option');
              optionDefault.value = optionDefault.textContent = "Sin cuotas";
              select.appendChild(optionDefault);
              data.forEach(function(item) {
                option = document.createElement('option');
                option.value = option.textContent = item;
                select.appendChild(option);
              });
              cuotas.appendChild(select);
            } else {
              window.credito = false;
              var cuotas = document.getElementById('selectCuotas');
              if (cuotas != undefined) {
                cuotas.parentNode.removeChild(cuotas);
              }
            }

          });

          element.on('change', function(data) {
            console.log('CHANGE: ', data);
            document.getElementById("msjNroTarjeta").style.display = "none";
            document.getElementById("msjFechaVencimiento").style.display = "none";
            document.getElementById("msjCvv").style.display = "none";
            if (data.length != 0) {
              data.forEach(function(d) {
                if (d['code'] == "invalid_number") {
                  document.getElementById("msjNroTarjeta").style.display = "block";
                  document.getElementById("msjNroTarjeta").innerText = d['message'];
                }
                if (d['code'] == "invalid_expiry") {
                  document.getElementById("msjFechaVencimiento").style.display = "block";
                  document.getElementById("msjFechaVencimiento").innerText = d['message'];
                }
                if (d['code'] == "invalid_cvc") {
                  document.getElementById("msjCvv").style.display = "block";
                  document.getElementById("msjCvv").innerText = d['message'];
                }
              });
            }
          })
        });

        // Cvv2
        window.cardCvv = payform.createElement(
          'card-cvc', {
            style: elementStyles,
            placeholder: 'CVV'
          },
          'txtCvv'
        );

        window.cardCvv.then(element => {
          element.on('change', function(data) {
            console.log('CHANGE CVV2: ', data);
          })
        });

        

        // Fecha de vencimiento
        window.cardExpiry = payform.createElement(
          'card-expiry', {
            style: elementStyles,
            placeholder: 'MM/AAAA'
          }, 'txtFechaVencimiento'
        );

        window.cardExpiry.then(element => {
          element.on('change', function(data) {
            console.log('CHANGE F.V: ', data);
          })
        });
        
      }, "json");
    }

    function pay() {
      $("#loading").show();

      var data = {
        name: $('#nombre').val(),
        lastName: $('#apellido').val(),
        email: $('#email').val(),
        alias: 'KS'
      }

      if (window.channel == "paycard") {
        data['userBlockId'] = Math.floor(Math.random() * 10000) + 1;
      } else if (window.channel == "web") {
        data['phoneNumber'] = '918273645';
        data['currencyConversion'] = window.dcc;
        data['recurrence'] = false;
        if (window.credito) {
          cuotaSeleccionada = $('#selectCuotas').val();
          if (cuotaSeleccionada == "Sin cuotas") {
            data['installment'] = 0;
          } else {
            data['installment'] = cuotaSeleccionada;
          }
        }
      }

        console.log(data);
        console.log('configuration: ', window.configuration);

        window.payform.createToken(
          [window.cardNumber, window.cardExpiry, window.cardCvv], data).then(function(data) {
          console.log('data create token: ', data);
          alert("BIN: " + data.bin + "\ntransactionToken: " + data.transactionToken + "\nchannel: " + data.channel);
          if (window.channel == "web") {
            $.post("api/authorization.php", {
              'transactionToken': data.transactionToken,
              'amount': window.amount,
              'purchase': window.purchase
            }, function(response){
              console.log(response);
              $("#loading").hide();
              if (response['dataMap'] != undefined) {
                if (response['dataMap']['ACTION_CODE'] == "000") {
                  alert('Pago aprobado');
                }
              } else if (response['data'] != undefined) {
                if (response['data']['ACTION_CODE'] != "000") {
                  alert('Pago denegado: ' + response['data']['ACTION_DESCRIPTION']);
                }
              }
            }, "json");
          } else if (window.channel == "paycard") {
            $.post("api/tokenization.php", {
              'transactionToken': data.transactionToken
            }, function(response){
              var json = JSON.parse(response);
              console.log(json);
              console.log(json['errorCode']);
              $("#loading").hide();
              if (json['errorCode'] == 0) {
                  alert('Tokenización exitosa\n*Tarjeta:' + json['card']['cardNumber']+'\n*Marca: '+ json['card']['brand']+'\n*TokenId: '+json['token']['tokenId']);
              } else {
                alert('Tokenización no exitosa\n*Motivo: ' + json['order']['actionDescription']);
              }
            }, "json");
          }
          
        }).catch(function(error) {
          console.log('data: ', error);
          $("#loading").hide();
          alert(error);
        });

      }

      window.onload = function(e) {
        $("#loading").hide();
      };

  </script>

</body>

</html>
