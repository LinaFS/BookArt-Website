<?php
    session_start();
    if (isset($_SESSION["usuario"])){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inicio Sesión - BookArt</title>
        <link rel="stylesheet" href="../CSS/reset.css">
        <link rel="stylesheet" href="../CSS/style.css">
        <link rel="stylesheet" href="../CSS/styleSesion.css">
        <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <script src="../JavaScript/funcionModal.js"></script>
    </head>

    <body>
        <main>
            <dialog class="iniciarSesion" id="modal">
                <!-- PANTALLA INICIAL -->
                <div class="Sesion" id="sesion">
                    <h1>Inicia Sesión o regístrate</h1>
                    <p class="pLeyenda">Usa tu correo para acceder a tu cuenta BookArt</p>
                    <button class="btnIS" id="btnis" onclick="crearCuenta()">Crear una cuenta</button>
                    <button class="btnIS" id="btn" onclick="abrirSesion()">Iniciar sesión</button>
                    <p class="condiciones">Al continuar, aceptas los Términos y condiciones de uso.<br>Consulta nuestra política de privacidad</p>
                </div>

                <!-- INICIO DE SESIÓN -->
                <div class="inicioSesion" id="is">
                    <h1>Inicio de Sesión</h1>
                    <form action="../PHP/login.php" method="POST">
                        <div style="position: relative;">
                            <p class="ingresaUsuario">Ingresa tu usuario o correo electrónico</p>
                            <input type="text" name="sesionUsuario" required>
                        </div>
                        <div style="position: relative;">
                            <p class="ingresaContra">Ingresa tu contraseña</p>
                            <span id="imgVerContrasena" class="material-symbols-outlined">visibility</span>
                            <input id="pass" type="password" name="sesionContra" required>
                        </div>
                        <p class="olvidaContra" onclick="reestablecerC()">He olvidado mi contraseña</p>
                        <button class="ISbtn" type="submit">Iniciar sesión</button>
                        <p class="faltaCuenta">¿Aún no tienes cuenta?<span class="creaCuentaDesdeInicio" onclick="crearCuenta()">Crear cuenta</span></p>
                    </form>
                </div>

                <!-- CREAR CUENTA -->
                <form id="cc" class="crearCuenta" action="../PHP/crear_cuenta.php" method="POST">
                    <h1>Crear una cuenta</h1>
                    
                    <div class="cNombre">
                        <p>Nombre</p>
                        <input type="text" id="name" name="nombre" data-error-id="etiquetaNombre" required>
                        <p class="etiquetaNombre">Debe empezar con mayúscula y no se aceptan números</p>
                    </div>
                    <div class="cApellidoP">
                        <p>Apellido Paterno</p>
                        <input id="firstname" type="text" name="paterno" data-error-id="etiquetaPaterno" required>
                        <p class="etiquetaPaterno">Debe empezar con mayúscula y no se aceptan números</p>
                    </div>
                    <div class="cApellidoM">
                        <p>Apellido Materno</p>
                        <input id="lastname" type="text" name="materno" data-error-id="etiquetaMaterno" required>
                        <p class="etiquetaMaterno">Debe empezar con mayúscula y no se aceptan números</p>
                    </div>

                    <div class="cUsuario">
                        <p>Nombre de usuario</p>
                        <input id="usuario" type="text" name="usuario" data-error-id="etiquetaUsuario" required>
                        <p class="etiquetaUsuario">Verifica tu usuario</p>
                    </div>
                    <div class="cTelefono">
                        <p>Teléfono</p>
                        <input id="tel" type="tel" name="tel" data-error-id="etiquetaTel" required>
                        <p class="etiquetaTel">Verifica tu teléfono</p>
                    </div>

                    <p class="tituloFechaN">Fecha de nacimiento</p>
                    <div class="cDia">
                        <p>Día</p>
                        <select name="Dia" id="dias" required>
                            <option value="01">1</option>
                            <option value="02">2</option>
                            <option value="03">3</option>
                            <option value="04">4</option>
                            <option value="05">5</option>
                            <option value="06">6</option>
                            <option value="07">7</option>
                            <option value="08">8</option>
                            <option value="09">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21</option>
                            <option value="22">22</option>
                            <option value="23">23</option>
                            <option value="24">24</option>
                            <option value="25">25</option>
                            <option value="26">26</option>
                            <option value="27">27</option>
                            <option value="28">28</option>
                            <option value="29">29</option>
                            <option value="30">30</option>
                            <option value="31">31</option>
                        </select>
                    </div>
                    <div class="cMes">
                        <p>Mes</p>
                        <select name="Mes" id="meses" required>
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="cAño">
                        <p>Año</p>
                        <input id="año" type="text" name="anio" required>
                        <p class="etiquetaAño">Verifica tu año de nacimiento</p>
                    </div>

                    <div class="cCorreo">
                        <p>Correo electrónico</p>
                        <input id="emailV" type="email" name="correo" placeholder="example@mail.com" data-error-id="etiquetaCorreo" required>
                        <p class="etiquetaCorreo">Verifica tu correo electrónico</p>
                    </div>

                    <div class="cContraseña">
                        <p>Contraseña</p>
                        <input id="pass1" type="password" name="contrasena" data-error-id="etiquetaContra" required>
                        <span id="imgVerContrasena1" class="material-symbols-outlined">visibility</span>
                        <p class="etiquetaContra">Debe contener 8 caracteres: números, símbolos y mayúsculas</p>
                        <p class="etiquetaCoincidenciaC">Las contraseñas no coinciden</p>
                    </div>
                    <div class="cCContraseña">
                        <p>Confirma tu contraseña</p>
                        <input id="pass2" type="password" name="ConfirmaContra" data-error-id="etiquetaContra2" required>
                        <span id="imgVerContrasena2" class="material-symbols-outlined">visibility</span>
                        <p class="etiquetaContra2">Debe contener 8 caracteres: números, símbolos y mayúsculas</p>
                        <p class="etiquetaCoincidenciaC">Las contraseñas no coinciden</p>
                    </div>

                    <input type="hidden" name="validado" id="validado" value="0">
                    
                    <div class="botonCrearCuenta">
                        <button id="buttonCC" type="submit" disabled>Crear cuenta</button>
                        <div class="verCuenta">
                            <p>¿Ya tienes una cuenta?</p>
                            <label class="cambiarInicio" onclick="cambiar()">Iniciar sesión</label>
                        </div>
                    </div>
                </form>

                <!-- RECUPERACIÓN DE CONTRASEÑA -->
                <form class="recuperacion" id="reestablecerContra" action="../PHP/reset_password.php" method="POST">
                    <h1>Recuperación de contraseña</h1>
                    <div class="contentR">
                        <p>Ingresa tu correo electrónico</p>
                        <input type="text" id="correoRecuperacion" name="correoRecupera" required>
                        <p class="etiquetaError">Revisa tu correo electrónico</p>
                        
                        <div style="position: relative; margin-top: 1.5rem;">
                            <p>Ingresa tu nueva contraseña</p>
                            <span id="imgVerContrasena3" class="material-symbols-outlined">visibility</span>
                            <input id="pass3" type="password" name="recuperaContra" required>
                            <p class="etiquetaContra">Debe contener 8 números, símbolos y mayúsculas</p>
                            <p class="etiquetaCoincidenciaC">Las contraseñas no coinciden</p>
                        </div>
                        
                        <div style="position: relative; margin-top: 1.5rem;">
                            <p>Confirma la contraseña</p>
                            <span id="imgVerContrasena4" class="material-symbols-outlined">visibility</span>
                            <input id="pass4" type="password" name="recuperaContra2" required>
                            <p class="etiquetaContra">Debe contener 8 números, símbolos y mayúsculas</p>
                            <p class="etiquetaCoincidenciaC">Las contraseñas no coinciden</p>
                        </div>
                    </div>
                    <div class="btnReestablecer">
                        <button type="submit" id="restabeceContra">Reestablecer</button>
                    </div>
                </form>

                <!-- IMAGEN LATERAL -->
                <div class="imgSesion">
                    <span id="cerrarSesion" class="material-symbols-outlined" onclick="reset()">close</span>
                    <img class="imgP" src="../img/PantallaSesion.png" alt="BookArt">
                </div>
            </dialog>
        </main>
    </body>
</html>