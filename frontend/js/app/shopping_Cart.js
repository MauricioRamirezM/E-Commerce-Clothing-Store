import { showAlert } from "./alert.js";



document.addEventListener('DOMContentLoaded', (e) => {
    const add_cart_btn = document.getElementById('add_cart_btn');
    document.addEventListener('click', (e) => {

        if (e.target === add_cart_btn) {
            const product_id = new URLSearchParams(window.location.search).get('product_id');
            const price = document.getElementById('product_price').textContent;
            const name = document.getElementById('product_name').textContent;
            const description = document.getElementById('product_description').textContent;
            const color = document.getElementById('product_color').textContent;
            const image = document.getElementById('product_image').getAttribute('src');
            const brand = document.getElementById('product_brand').textContent;
            const category = document.getElementById('product_category').textContent;
            const size = document.getElementById('product_size_select').value;
            if (!size) {
                showAlert("Please select a size", 'danger');
                return;
            }
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existing = cart.find(item => item.product_id === product_id && item.size === size);
            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({
                    product_id,
                    price,
                    name,
                    description,
                    color,
                    category,
                    image,
                    brand,
                    size,
                    quantity: 1,
                });
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            showAlert("Product added to cart.")
            render_cart(cart);
        }
        if (e.target.closest('.trash_btn')) {
            e.preventDefault();
            e.stopPropagation();
            const trash_btn = e.target.closest('.trash_btn');
            delete_cart_item(trash_btn);
        }
    })



    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart) {
        render_cart(cart);

    }

     const cart_dashboard_section = document.getElementById('cart_dashboard_container');
     if(cart_dashboard_section){
        display_cart_dashboard();
     }


})


function render_cart(cart) {
    clearCartItems();
    let total = 0;
    const cart_footer = document.getElementById('total_cart_li');
    if (cart.length > 0) {
        if (cart_footer) cart_footer.style.display = '';
        cart.forEach(ele => {
            const cart_item = `
                <li>
                    <div class="cart_details" id="cart_details">
                        <div class="cart_img_wrapper" style="max-width: 4rem;">
                            <img src="${ele.image}" alt="">
                        </div>
                        <div class="cart_details_wrapper">
                            <p id="product_name_cart">${ele.name}</p>
                            <p id="category_name_cart">${ele.category}</p>
                            <p id="size_cart">size: ${ele.size}</p>
                            <p>Quantity: <span id="quantity">${ele.quantity}</span></p>
                        </div>
                        <div class="cart_price_wrapper">
                            <button type="button" value="delete" id="delete_from_cart_btn"
                                data-product_id="${ele.product_id}" data-size="${ele.size}" class="trash_btn"><i class="bi bi-trash3"></i></button>
                            <p id="price_cart"> ${ele.price}</p>
                        </div>
                    </div>
                </li>
            `;
            if (cart_footer) {
                cart_footer.insertAdjacentHTML('beforebegin', cart_item);
                let price = parseFloat(ele.price.toString().replace(/[^\d.]/g, ""));
                total += price * ele.quantity;
            }
        });
        const totalCartElem = document.getElementById('total_cart');
        if (totalCartElem) {
            totalCartElem.textContent = `${total.toFixed(2)} €`;
        }
    } else {
        if (cart_footer) cart_footer.style.display = 'none';
    }
}



function clearCartItems() {
    const cartList = document.querySelector('.cart_list');
    if(cartList){
        Array.from(cartList.querySelectorAll('li')).forEach(li => {
        if (!li.classList.contains('dropdown-header') && li.id !== 'total_cart_li') {
            li.remove();
        }
    });
    }
    
}

function delete_cart_item(button) {
    const productId = button.getAttribute('data-product_id');
    const size = button.getAttribute('data-size');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    cart = cart.filter(item => !(item.product_id === productId && item.size === size));
    localStorage.setItem('cart', JSON.stringify(cart));
    render_cart(cart);
}




export function display_cart_dashboard() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let product_row = '';
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
                        <span>${product.brand}</span>${product.color} <span></span>
                    </div>
                </div>
            </td>
            <td class="price_td"><span>${product.price} €</span></td>
            <td class="quantity_td">
                <div class="btn-group" role="group" aria-label="Basic outlined example">
                    <button type="button" name="minus_btn"  data-size="${product.size}" data-product_id="${product.product_id}"  class="btn modify_btn quantity_btn"> - </button>
                    <button value="${product.quantity}" type="button" class="btn quantity_btn"><span>${product.quantity}</span></button>
                    <button type="button" name="plus_btn"  data-size="${product.size}" data-product_id="${product.product_id}"  class="btn modify_btn quantity_btn"> + </button>
                </div>
            </td>
            <td class="subtotal_td">
                <span>${subtotal.toFixed(2)} € </span>
            </td>
            <td class="trash_td"><button type="button" data-size="${product.size}" data-product_id="${product.product_id}" class="btn trash_btn"><i class="bi bi-trash3"></i></button></td>
        </tr>
        `;
    });
    const product_table = document.getElementById('product_table');
    if(product_table){
        product_table.innerHTML = product_row;
    }
    fill_order_summary(cart, 'resume_subtotal', 'resume_total');

}
export function fill_order_summary(cart, subtotalId, totalId, countId) {
    let total = 0;
    let productCount = 0;
    cart.forEach(product => {
        let price = parseFloat(product.price.toString().replace(/[^\d.]/g, ""));
        total += price * product.quantity;
        productCount += product.quantity;
    });
    const subtotalElem = document.getElementById(subtotalId);
    const totalElem = document.getElementById(totalId);
    if (subtotalElem) subtotalElem.textContent = `${total.toFixed(2)} €`;
    if (totalElem) totalElem.textContent = `${total.toFixed(2)} €`;
    if (countId) {
        const countElem = document.getElementById(countId);
        if (countElem) countElem.textContent = `Products: ${productCount}`;
    }
}
export function clear_cart() {
    localStorage.removeItem('cart'); 
    render_cart([]); 
    display_cart_dashboard(); 
}








