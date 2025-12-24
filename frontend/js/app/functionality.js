import { display_cart_dashboard } from "./shopping_Cart.js";
import { clear_cart } from "./shopping_Cart.js";
import { showConfirm } from "./alert.js";
document.addEventListener('DOMContentLoaded', () => {
    update_header_for_user();

    document.addEventListener('click', (e) => {
         if (e.target.matches('button[name="category_btn"]')) {
            const category = e.target.value;
            window.location.href = `./all_products.html?category=${encodeURIComponent(category)}`;
        }


        if (e.target.matches('.modify_btn')) {
            const product_id = e.target.dataset.product_id;
            const size = e.target.dataset.size;
            if (e.target.name === 'minus_btn') { update_cart_quantity(product_id, size, -1) }
            if (e.target.name === 'plus_btn') { update_cart_quantity(product_id, size, 1) }
        }
        if (e.target.id === 'clear_cart_btn') {
            showConfirm('Are you sure you want to clear the cart?', () => {
                clear_cart();
                window.location.href = 'http://localhost/e-commerce/frontend/html/app/all_products.html';
            });
        }
        if (e.target.classList.contains('search_header_btn')) {
            const serach_bar2 = document.getElementById("serach_bar2");
            if (serach_bar2) {
                serach_bar2.style.display = (serach_bar2.style.display === 'block') ? 'none' : 'block';
            }
        }
    });
    get_all_filters().then(response => {
        let allFilters = response.data;
        const grouped = group_subAttr_by_attribute(allFilters.sub_attr);
        const colorSelectIds = ['color_select', 'color_select_modal'];
        const brandSelectIds = ['brand_select', 'brand_select_modal'];
        const styleSelectIds = ['style_select', 'style_select_modal'];
        const materialSelectIds = ['material_select', 'material_select_modal'];
        const sizeSelectIds = ['size_select', 'size_select_modal'];

        colorSelectIds.forEach(id => fillSelectOptions(id, grouped['Color Group'], 'name', 'name'));
        brandSelectIds.forEach(id => fillSelectOptions(id, allFilters.brands, 'brand_name', 'brand_name'));
        styleSelectIds.forEach(id => fillSelectOptions(id, grouped['Fit'], 'name', 'name'));
        materialSelectIds.forEach(id => fillSelectOptions(id, grouped['Material'], 'name', 'name'));
        sizeSelectIds.forEach(id => fillSelectOptions(id, allFilters.clothing_sizes, 'size', 'size'));

        // If you want to add 'Style' group options as well:
        styleSelectIds.forEach(id => {
            grouped['Style'].forEach(ele => {
                const styleOption = document.createElement("option");
                styleOption.value = ele.name;
                styleOption.textContent = ele.name;
                const select = document.getElementById(id);
                if (select) select.appendChild(styleOption);
            });
        });

    })
    get_all_categories().then(response => {
        let categories = response.data;


        categories.forEach(ele => {
            const menuButton = document.createElement('a');
            const li = document.createElement('li');
            li.appendChild(menuButton);
            menuButton.setAttribute('href', `http://localhost/e-commerce/frontend/html/app/all_products.html?category=${ele.category_name}`);
            // menuButton.setAttribute('value', ele.category_name);
            menuButton.textContent = ele.category_name
            menuButton.classList.add('btn', 'menu_category_btn');
            const menu = document.getElementById('menu_list_categories');
            if (menu) menu.appendChild(li);
            const mainButton = document.createElement('button');
            mainButton.setAttribute('name', 'category_btn');
            mainButton.setAttribute('value', ele.category_name);
            mainButton.textContent = ele.category_name;
            mainButton.classList.add('btn');
            const wrapper = document.getElementById('category_btn_wrapper');
            if (wrapper) wrapper.appendChild(mainButton);
        })


    });


})





async function get_all_categories() {
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/categories_controller.php?catego=1');
        let json_res = await response.json();
        return json_res;
    } catch (error) {
        console.log(error);
    }
}
async function get_all_filters() {
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?characteristics=1');
        let json_res = await response.json();
        return json_res;

    } catch (error) {

    }
}

function group_subAttr_by_attribute(arr) {
    return arr.reduce((groups, item) => {
        const key = item.attribute;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(item);
        return groups;
    }, {});
}
export function fillSelectOptions(selectEle, options, value, text) {
    const select = document.getElementById(selectEle);
    if (!select) return;
    select.length = 1;
    options.forEach(opt => {
        const option = document.createElement("option");
        option.value = opt[value];
        option.textContent = opt[text];
        select.appendChild(option);
    });
}

function update_cart_quantity(productId, size, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.map(item => {
        if (item.product_id == productId && item.size == size) {
            let newQuantity = item.quantity + change;
            return { ...item, quantity: newQuantity > 0 ? newQuantity : 1 };
        }
        return item;
    });
    localStorage.setItem('cart', JSON.stringify(cart));
    display_cart_dashboard();
}

export function validate_form_fields(form) {
    if (!form) return false;
    let isFormValid = true;
    const requiredInputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    requiredInputs.forEach(input => {
        let isValid = true;
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            input.setCustomValidity('This field is required');
        } else if (input.hasAttribute('pattern')) {
            const pattern = input.getAttribute('pattern');
            const regex = new RegExp(pattern);
            if (!regex.test(input.value)) {
                isValid = false;
                input.classList.add('is-invalid');
                input.setCustomValidity(input.title || 'Invalid input');
            } else {
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
            }
        } else {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
        }
        if (!isValid) isFormValid = false;
    });
    return isFormValid;
}

function update_header_for_user() {
    const profile_dropdown = document.getElementById('profile_dropdown');
    const checkout_cart_btn = document.getElementById('checkout_cart_btn');

    let is_logged = localStorage.getItem('is_logged') === 'true';
    let user_info = (is_logged )? JSON.parse(localStorage.getItem('user_info')) : null;
    let points = user_info ? user_info.points : 0;

    const brand_logo = document.getElementById('logo_link_wraper');
    let points_box = `
        <div class="missing_points_wrapper">
            <span id="missing_points_span_header" class="missing_points_span_header">
                <i class="bi bi-coin"></i> : ${points}
            </span>
        </div>`;
    if (brand_logo && is_logged) {
        brand_logo.insertAdjacentHTML('beforebegin', points_box);
    }
    const index_login_wrapper =document.getElementById('index_login_wrapper');
    if(index_login_wrapper && is_logged){
        index_login_wrapper.classList.add('hide_ele');
    }

    if (checkout_cart_btn) {
        checkout_cart_btn.href = is_logged ? 'http://localhost/e-commerce/frontend/html/app/cart_dashboard.html' : 'http://localhost/e-commerce/frontend/html/app/login.html';
    }

    let button_path = (is_logged) ? 'http://localhost/e-commerce/frontend/html/app/profile.html' : 'http://localhost/e-commerce/frontend/html/app/login.html';
    let button_text = (is_logged) ? 'Profile' : 'Log in'
     let login_notice = (is_logged) ? "": `<p  class="text-body-secondary text-center"><small>Log in to complete your purchase</small></p>`;
    let points_li = `<li id="missing_point_li">
        <i class="bi bi-coin me-1"></i>
        <span id="missing_points_span_dropdown" class="mb-1"> Missing points: ${points}</span>
    </li>`;
    let profile_btn_li = `<li>
        <a name="profile_dropdown_btn" id="profile_dropdown_btn" class="btn "
            href="${button_path}" role="button">${button_text}</a>
             ${login_notice}
    </li>`;
    let logout_btn =  `<li>
                                <button id="logout_btn" class=" btn logout_btn"   type="button" >
                                    Log out 
                                </button>
                            </li>`

    
    if (profile_dropdown) {
        profile_dropdown.insertAdjacentHTML('beforeend', profile_btn_li);
        if(is_logged){
            const profile_btn = document.getElementById('profile_header');
            profile_dropdown.insertAdjacentHTML('beforeend', logout_btn);
            profile_btn.insertAdjacentHTML('afterend', points_li);
           
        }
    }
}


