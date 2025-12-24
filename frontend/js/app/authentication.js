import { showAlert } from './alert.js'
import { validate_form_fields } from './functionality.js'

document.addEventListener('DOMContentLoaded', (e) => {

    document.addEventListener('submit', (e) => {
        if (e.target.id === 'register_form') {
            e.preventDefault();
            if (validate_form_fields(e.target)) {
                authenticate(e.target, 'register').then((result) => {
                    if (result.status === 'error') {
                            showAlert(result.message, 'danger');
                            setTimeout(() => {
                            location.reload();
                        }, 2000)
                        return;
                    }else{
                        let user_info = JSON.stringify(result.data.user_info);
                        let user_orders = JSON.stringify(result.data.orders);
                        let user_addresses = JSON.stringify(result.data.addresses);
                        localStorage.setItem('is_logged', 'true');
                         localStorage.setItem('user_orders', user_orders);
                        localStorage.setItem('user_addresses', user_addresses);
                        localStorage.setItem('user_info', user_info);
                        let message = ` Welcome! You've joined Missing Brand and earned  <i class="bi bi-coin"></i>  2000 points to spend on your favorite products!<br>${result.message}`;
                        showAlert(message, 'success');
                        setTimeout(() => {
                            window.location.href = 'http://localhost/e-commerce/index.html';
                        }, 7000);
                    }
                });
            }
        }
        if (e.target.id === 'login_form') {
            e.preventDefault();
            if (validate_form_fields(e.target)) {
                authenticate(e.target, 'login').then((result) => {
                    if (result.status === 'error') {;
                        showAlert(result.message, 'danger');
                        setTimeout(() => {
                            location.reload();
                        }, 2000)
                        return;
                    } else {
                        let user_orders = JSON.stringify(result.data.orders);
                        let user_addresses = JSON.stringify(result.data.addresses);
                        let user_info = JSON.stringify(result.data.user_info);
                        localStorage.setItem('user_orders', user_orders);
                        localStorage.setItem('user_addresses', user_addresses);
                        localStorage.setItem('user_info', user_info);
                        localStorage.setItem('is_logged', 'true');
                        showAlert(result.message, 'success');
                        setTimeout(() => {
                            window.location.href = 'http://localhost/e-commerce/frontend/html/app/all_products.html';
                        }, 1500)
                    }
                })
            }
        }
    })

    document.addEventListener('click', (e) => {
        if (e.target.id === 'logout_btn') {
            user_logout().then((result) => {
                if (result.status === 'success') {
                    localStorage.removeItem('user_info');
                    localStorage.removeItem('user_addresses');
                    localStorage.removeItem('user_orders');
                    localStorage.removeItem('cart');
                    localStorage.removeItem('is_logged');
                    showAlert(result.data, 'success');
                    setTimeout(() => {
                        window.location.href = "http://localhost/e-commerce/frontend/html/app/all_products.html";
                    }, 1500)
                } else {
                    showAlert(result.data, 'danger');

                }
            })
        }
    })

})


async function authenticate(form, action) {
    let form_data = new FormData(form);
    let data = Object.fromEntries(form_data.entries());
    data.action = action;
    if (action === 'register') {
        data.points = 200;
    }
    let options = {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    };
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/authentication_controller.php', options);
        let json_res = await response.json();
        return json_res;
    } catch (error) {
        console.log(error);
    }
}

async function user_logout() {
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/logout_controller.php');
        let json_res = await response.json()
        return json_res;
    } catch (error) {
        console.log(error)
    }


}





