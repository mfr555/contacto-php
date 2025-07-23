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
  <style>
    #mensaje { margin-top: 15px; font-weight: bold; }
    #mensaje.ok { color: green; }
    #mensaje.error { color: red; }
  </style>
</head>
<body>
  <form id="form-contacto">
    <input type="text" name="nombre" placeholder="Nombre" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <textarea name="mensaje" placeholder="Mensaje" required></textarea><br>
    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
    <button type="submit">Enviar</button>
  </form>

  <div id="mensaje"></div>

  <script>
    const form = document.getElementById('form-contacto');
    const mensajeDiv = document.getElementById('mensaje');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      mensajeDiv.textContent = "Enviando...";
      mensajeDiv.className = "";

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
              mensajeDiv.textContent = data.message;
              mensajeDiv.className = "ok";
              form.reset();
            } else {
              mensajeDiv.textContent = data.message;
              mensajeDiv.className = "error";
            }
          })
          .catch(err => {
            mensajeDiv.textContent = "Ocurrió un error inesperado.";
            mensajeDiv.className = "error";
          });
        });
      });
    });
  </script>
</body>
</html>
