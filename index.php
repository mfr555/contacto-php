<?php
  require 'vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  $siteKey = htmlspecialchars($_ENV['RECAPTCHA_SITEKEY'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Formulario AJAX</title>
  <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $siteKey; ?>"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="row">
    <div class="col-lg-3 col-md-4 col-12">
      <form id="form-contacto">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <textarea name="mensaje" class="form-control" placeholder="Mensaje" required></textarea>
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
        <button type="submit" class="btn btn-primary">Enviar</button>

        <div id="form-response"></div>
      </form>      
    </div>
  </div>
  
  <script>
    const form = document.getElementById('form-contacto');
    const responseDiv = document.getElementById('form-response');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      responseDiv.textContent = "Enviando...";
      responseDiv.className = "";

      grecaptcha.ready(function () {
        grecaptcha.execute("<?php echo $siteKey; ?>", {action: 'formulario'}).then(function (token) {
          const formData = new FormData(form);
          formData.set('g-recaptcha-response', token);

          fetch('procesar.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              responseDiv.textContent = data.message;
              responseDiv.className = "text-success";
              form.reset();
            } else {
              responseDiv.textContent = data.message;
              responseDiv.className = "text-error";
            }
          })
          .catch(err => {
            responseDiv.textContent = "Ocurrió un error inesperado: " + err.message;
            responseDiv.className = "text-error";
          });
        });
      });
    });
  </script>
</body>
</html>
