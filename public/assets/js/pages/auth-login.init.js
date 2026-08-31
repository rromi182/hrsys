/*
Template Name: StarCode & Dashboard Template
Author: StarCode Kh
Version: 1.1.0
Website: https://StarCode Kh.in/
Contact: StarCode Kh@gmail.com
File: auth login init Js File
*/

document.addEventListener('DOMContentLoaded', function () {
    const signInForm = document.getElementById('signInForm');
    
    // Solo ejecutar si estamos en la página de login
    if (!signInForm) return;

    signInForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        const strongPasswordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

        const usernameError = document.getElementById('username-error');
        const passwordError = document.getElementById('password-error');
        const successAlert = document.getElementById('successAlert');
        const rememberMeCheckbox = document.getElementById('checkboxDefault1');
        const rememberError = document.getElementById('remember-error');

        usernameError.classList.add('hidden');
        passwordError.classList.add('hidden');
        successAlert.classList.add('hidden');

        if (!emailRegex.test(username)) {
            usernameError.classList.remove('hidden');
        } else if (!strongPasswordRegex.test(password)) {
            passwordError.classList.remove('hidden');
        } else {
            successAlert.classList.remove('hidden');
        }

        if (!rememberMeCheckbox.checked) {
            event.preventDefault();
            rememberError.classList.remove('hidden');
        } else {
            rememberError.classList.add('hidden');
        }
    });
});