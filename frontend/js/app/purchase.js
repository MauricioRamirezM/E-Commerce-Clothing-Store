import { fill_order_summary } from "./shopping_Cart.js";
import { clear_cart } from "./shopping_Cart.js";
import { validate_form_fields } from "./functionality.js";
import { showAlert } from "./alert.js";


window.addEventListener('pageshow', updateStepStyles());
document.addEventListener('DOMContentLoaded', () => {
    updateStepStyles();
    const address_container = document.getElementById('shipping_address_container');
    const payment_container = document.getElementById('payment_container');
    const form = document.getElementById('shipping_address_form');
    if (address_container || payment_container) {
        let cart = JSON.parse(localStorage.getItem('cart'));
        fill_order_summary(cart, 'resume_subtotal', 'resume_total', 'quantity_span');
    }

    document.addEventListener('submit', (e) => {
        if (e.target === form) {
            if (!validate_form_fields(form)) {
                return;
            }
            const form_data = new FormData(form);
            const address_data = Object.fromEntries(form_data.entries());
            localStorage.setItem('checkout_address', JSON.stringify(address_data));
            localStorage.setItem('address_checked', 'true');
            window.location.href = './payment.html';

        }
    })
    const method_boxes = document.querySelectorAll('.method');
    method_boxes.forEach(box => {
        box.addEventListener('click', function (e) {
            if (e.target.classList.contains('form-check-input')) return;
            const radio = box.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }

            if(e.target.id !== "payment_missingpoint_wrapper"){
                showAlert("This payment method is currently unavailable." , 'danger')
            }
        });
    });

    const review_order = document.getElementById('review_order_container');
    if (review_order) {
        display_order_review();
    }

    document.addEventListener('click', (e) => {

        if(e.target.id === 'checkout_cart_page_btn'){
            let cart = JSON.parse(localStorage.getItem('cart'));
            check_availbility(cart).then((response)=>{
                if(response.status === 'error'){
                    showAlert(response.data, 'danger');
                }else{
                    window.location.href = './shipping_addres.html'
                }
                    
                
            })
        }


        if (e.target.id === 'payment_continue_btn') {
            const checked = document.querySelector('.form-check-input:checked');
            if (!checked) {
                showAlert('Please select a payment method.', 'danger');
                return;
            }
            let user_points = JSON.parse(localStorage.getItem('user_info')).points;
            let price_cart = JSON.parse(localStorage.getItem('cart'));
            let total = 0;
            price_cart.forEach(product => {
                let price = parseFloat(product.price.toString().replace(/[^\d.]/g, ""));
                total += price * product.quantity;
            });
            if (user_points < total) {
                showAlert('Not enogth Missing points.', 'danger');
                return;
            }
            const payment_method = {
                payment_id: checked.value,
                payment_name: checked.dataset.name
            }
            localStorage.setItem('payment_method', JSON.stringify(payment_method));
            localStorage.setItem('payment_checked', 'true');
            window.location.href = './review_order.html';


        }
        if (e.target.id == 'complete_purchase_btn') {
            let cart = JSON.parse(localStorage.getItem('cart'));
            let address = JSON.parse(localStorage.getItem('checkout_address'));
            let payment = JSON.parse(localStorage.getItem('payment_method'));
            send_purchase_data(address, cart, payment)


        }
    });

});

async function check_availbility(cart) {
     let options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart,
            available: true
        }
        )
    }
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/checkout_controller.php', options);
        let json_res = await response.json();
        return json_res;
    } catch (error) {
            console.log(error);
    }
}

async function send_purchase_data(address, cart, payment_method) {
    let options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            address,
            cart,
            payment_method,
            checkout : true
        })
    }
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/checkout_controller.php', options);
        let json_res = await response.json();
        if (json_res.status === 'error') {
            showAlert(json_res.message, 'danger');
            return;
        } else {
            let user_info = json_res.data.user_info;
            let addresses = json_res.data.addresses;
            let orders = json_res.data.orders;
            localStorage.setItem('user_info', JSON.stringify(user_info));
            localStorage.setItem('user_addresses', JSON.stringify(addresses));
            localStorage.setItem('user_orders', JSON.stringify(orders));
            showAlert(json_res.message, 'success');
            setTimeout(() => {
                window.location.href = './confirmation.html';
            }, 2000);
            clear_cart();
        }
    } catch (error) {
        console.log(error);
    }
}

function display_order_review() {
    const products_table = document.getElementById('order_review_table');
    const addres_card = document.getElementById('address_card_review');
    const payment_card = document.getElementById('payment_card_review');
    const resume_card = document.getElementById('review_resume_body');
    let product_row = '';
    let cart = JSON.parse(localStorage.getItem('cart'));
    let address = JSON.parse(localStorage.getItem('checkout_address'));
    let payment = JSON.parse(localStorage.getItem('payment_method'));
    if (cart) {
        cart.forEach(product => {
            let price = parseFloat(product.price.toString().replace(/[^\d.]/g, ""));
            let subtotal = price * product.quantity;
            product_row += `
        <tr>
            <td class="product_details_td">
                <div class="cart_img " style="max-width: 5rem;">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="cart_description">
                    <h6>${product.name}</h6>
                    <p>${product.description}</p>
                    <div class="brand_color">
                        <span>${product.brand}</span> ${product.color} <span></span>
                    </div>
                </div>
            </td>
            <td class="price_td"><span>${product.price} €</span></td>
            <td class="quantity_td">
                <span>${product.quantity}</span>
            </td>
            <td class=" subtotal_td review_total"> ${subtotal} €</td>
        </tr>
        `;
        })
        products_table.innerHTML = product_row;

    }
    if (address) {
        let address_data = `
            <p class="h6">${address.address_street}, ${address.address_ext_num}, ${address.address_int_num} </p>
            <p class="h6">${address.address_city}, ${address.address_state}, ${address.address_cp}</p>
            <p class="h6">${address.address_country}</p>
            <p class="h6">${address.address_phone},</p>
        `
        addres_card.innerHTML = address_data;
    }

    if (payment) {
        payment_card.innerHTML = `
            <h5>${payment.payment_name}</h5>
        `
    }
    if (resume_card) {
        fill_order_summary(cart, 'review_resume_subtotal', 'review_resume_total');
    }



}


function updateStepStyles() {

    const address_link = document.querySelector('.address_link');
    const payment_link = document.querySelector('.payment_link');
    const current_page = window.location.pathname.split('/').pop();

    if (current_page === 'shipping_addres.html') {
        localStorage.removeItem('address_checked');
        localStorage.removeItem('payment_checked');
        if (address_link && payment_link) {
            address_link.classList.remove('active');
            payment_link.classList.remove('active');
        }
    }
    if (current_page === 'payment.html') {
        localStorage.removeItem('payment_checked');
        if (address_link) address_link.classList.add('active');
        if (payment_link) payment_link.classList.remove('active');
    }
    if (localStorage.getItem('address_checked') === 'true' && address_link) {
        address_link.classList.add('active');
    }
    if (localStorage.getItem('payment_checked') === 'true' && payment_link) {
        payment_link.classList.add('active');
    }
    if (current_page === 'payment.html' && !localStorage.getItem('address_checked')) {
        window.location.href = './shipping_addres.html';
    }
    if (current_page === 'review_order.html' &&
        (!localStorage.getItem('address_checked') || !localStorage.getItem('payment_checked'))) {
        window.location.href = './payment.html';
    }
}
