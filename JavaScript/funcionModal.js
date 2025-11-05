document.addEventListener("DOMContentLoaded", function() {
    const modal = document.querySelector("#modal");
    const CerrarModal = document.querySelector("#cerrarSesion");
    const pass= document.getElementById("pass");
    const pass1= document.getElementById("pass1");
    const pass2= document.getElementById("pass2");
    const pass3= document.getElementById("pass3");
    const pass4= document.getElementById("pass4");
    const icon= document.getElementById("imgVerContrasena");
    const icon1= document.getElementById("imgVerContrasena1");
    const icon2= document.getElementById("imgVerContrasena2");
    const icon3= document.getElementById("imgVerContrasena3");
    const icon4= document.getElementById("imgVerContrasena4");
    // Obtiene el valor del parámetro de consulta "origen" de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const origen = urlParams.get("origen");

    modal.showModal();

    // Agrega un event listener al botón de cerrar modal
    CerrarModal.addEventListener("click", () => {
        // Redirige al usuario a la página de origen
        if (origen) {
            window.location.href = origen + ".php";
        } else {
            // Si no se especificó la página de origen, redirige a una página predeterminada
            window.location.href = "../index.php";
        }
    });

    icon.addEventListener("click", (e) => {
        if(pass.type==="password"){
            pass.type="text";
        }else{
            pass.type="password";
        }
    });

    icon1.addEventListener("click", (e) => {
        if(pass1.type==="password"){
            pass1.type="text";
        }else{
            pass1.type="password";
        }
    });

    icon2.addEventListener("click", (e) => {
        if(pass2.type==="password"){
            pass2.type="text";
        }else{
            pass2.type="password";
        }
    });

    icon3.addEventListener("click", (e) => {
        if(pass3.type==="password"){
            pass3.type="text";
        }else{
            pass3.type="password";
        }
    });

    icon4.addEventListener("click", (e) => {
        if(pass4.type==="password"){
            pass4.type="text";
        }else{
            pass4.type="password";
        }
    });

});

document.addEventListener("DOMContentLoaded",function(){
    const passwordRegex = /^(?=.*[0-9])(?=.*[A-Za-z])[\w!@#$%^&*()_+=-]+$/;
    const emailRegex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}(?:\.[A-Za-z]+)?$/;
    const phoneRegex = /^(?:\d\s?){10}$/;
    const nameRegex = /^[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ\s'-]+$/;

    const buttonCC = document.getElementById("buttonCC");

    let validado = false;

    const nameInput = document.getElementById("name");
    const paternoInput = document.getElementById("firstname");
    const maternoInput = document.getElementById("lastname");
    const emailInput = document.getElementById("emailV");
    const password1Input = document.getElementById("pass1");
    const password2Input = document.getElementById("pass2");
    const phoneInput = document.getElementById("tel");

    nameInput.addEventListener("input", function () {
        const inputValue = nameInput.value;
        validado = nameRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaNombre");
        enableDisableButton();
    });

    paternoInput.addEventListener("input", function () {
        const inputValue = paternoInput.value;
        validado = nameRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaPaterno");
        enableDisableButton();
    });

    maternoInput.addEventListener("input", function () {
        const inputValue = maternoInput.value;
        validado = nameRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaMaterno");
        enableDisableButton();
    });

    emailInput.addEventListener("input", function () {
        const inputValue = emailInput.value;
        validado = emailRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaCorreo");
        enableDisableButton();
    });

    password1Input.addEventListener("input", function () {
        const inputValue = password1Input.value;
        validado = passwordRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaContra");
        enableDisableButton();

        const pass1Value = password1Input.value;
        const pass2Value = password2Input.value;

        if (pass1Value !== pass2Value) {
            toggleVisibility(false, "etiquetaCoincidenciaC");
            validado = false;
        } else {
            toggleVisibility(true, "etiquetaCoincidenciaC");
            enableDisableButton();
        }
    });

    password2Input.addEventListener("input", function () {
        const inputValue = password2Input.value;
        validado = passwordRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaCoincidenciaC");
        enableDisableButton();

        const pass1Value = password1Input.value;
        const pass2Value = password2Input.value;

        if (pass1Value !== pass2Value) {
            toggleVisibility(false, "etiquetaCoincidenciaC");
            validado = false;
        } else {
            toggleVisibility(true, "etiquetaCoincidenciaC");
            enableDisableButton();
        }
    });

    phoneInput.addEventListener("input", function () {
        const inputValue = phoneInput.value;
        validado = phoneRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaTel");
        enableDisableButton();
    });

    function toggleVisibility(isValid, className) {
        const element = document.getElementsByClassName(className)[0];
        element.style.visibility = isValid ? 'hidden' : 'visible';
    }

    function enableDisableButton() {
        buttonCC.disabled = !validado;
    }
});

document.addEventListener("DOMContentLoaded", function(){
    const passwordRegex = /^(?=.*[0-9])(?=.*[A-Za-z])[\w!@#$%^&*()_+=-]+$/;
    const emailRegex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}(?:\.[A-Za-z]+)?$/;
    const yearRegex = /^(19[3-9]\d|20[0-2]\d)$/;

    const restablece=document.getElementById("restableceContra");

    const emailInput=document.getElementById("correoRecuperacion");
    const password3 =document.getElementById("pass3");
    const password4 = document.getElementById("pass4");
    const yearInput = document.getElementById("año"); // Nuevo campo para el año

    emailInput.addEventListener("input", function () {
        const inputValue = emailInput.value;
        validado = emailRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaError");
        enableDisableButton();
    });

    password3.addEventListener("input", function () {
        const inputValue = password3.value;
        validado = passwordRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaContra");
        enableDisableButton();

        const pass1Value = password1Input.value;
        const pass2Value = password2Input.value;

        if (pass1Value !== pass2Value) {
            toggleVisibility(false, "etiquetaCoincidenciaC");
            validado = false;
        } else {
            toggleVisibility(true, "etiquetaCoincidenciaC");
            enableDisableButton();
        }
    });

    password4.addEventListener("input", function () {
        const inputValue = password4.value;
        validado = passwordRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaCoincidenciaC");
        enableDisableButton();

        const pass1Value = password1Input.value;
        const pass2Value = password2Input.value;

        if (pass1Value !== pass2Value) {
            toggleVisibility(false, "etiquetaCoincidenciaC");
            validado = false;
        } else {
            toggleVisibility(true, "etiquetaCoincidenciaC");
            enableDisableButton();
        }
    });

    yearInput.addEventListener("input", function () {
        const inputValue = yearInput.value;
        validado = yearRegex.test(inputValue);
        toggleVisibility(validado, "etiquetaAño");
        enableDisableButton();
    });
    

    function toggleVisibility(isValid, className) {
        const element = document.getElementsByClassName(className)[0];
        element.style.visibility = isValid ? 'hidden' : 'visible';
    }

    function enableDisableButton() {
        restablece.disabled = !validado;
    }

});

    

function abrirSesion(){
    ocultar();
    mostrar();
}

function crearCuenta(){
    ocultar();
    ocultarS();
    mostrarS();
}

function cambiar(){
    ocultarCC();
    mostrar();
    resetForm();
}

function reset(){
    mostrarInicio();
    ocultarCC();
    ocultarS();
}

function reestablecerC(){
    ocultarS();
    mostrarR();
}

function reestablece(){
    ocultarR();
    mostrar();
}

function mostrarInicio(){
    document.getElementById('sesion').style.display='inline-block';
}

function ocultar(){
    document.querySelector("#sesion").style.display='none';
}

function ocultarS(){
    document.querySelector("#is").style.display='none';
}

function mostrar(){
    document.getElementById('is').style.display='inline-block';
}

function mostrarS(){
    document.getElementById('cc').style.display='inline-block';
}

function ocultarCC(){
    document.getElementById('cc').style.display='none';
}

function mostrarR(){
    document.getElementById('reestablecerContra').style.display='inline-block';
}

function ocultarR(){
    document.getElementById('reestablecerContra').style.display='none';
}

function verContrasena(){
    document.getElementById('imgVerContrasena').style.display='inline-block';
}

function resetForm() {
    document.getElementById("name").value = "";
    document.getElementById("firstname").value = "";
    document.getElementById("lastname").value = "";
    document.getElementById("usuario").value = "";
    document.getElementById("email").value = "";
    document.getElementById("pass1").value = "";
    document.getElementById("pass2").value = "";
    document.getElementById("tel").value = "";
    document.getElementById("dias").value = "01";
    document.getElementById("meses").value = "01";
    document.getElementById("año").value = "";
}