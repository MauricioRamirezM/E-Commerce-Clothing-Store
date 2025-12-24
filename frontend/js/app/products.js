import { fillSelectOptions } from "./functionality.js";



document.addEventListener('DOMContentLoaded', () => {
    const card_template = document.getElementById('card_template');
    const product_section = document.getElementById('all_products_section');
    const params = new URLSearchParams(window.location.search);
    const category = params.get('category');

    if (category) {
        get_product_by_category(category).then(category => {
            if (category) {
                if (card_template) {
                    make_product_card(category, card_template, product_section);
                }
            }
        })
    } else {

        get_all_products().then(all_products => {
            if (all_products) {
                if (card_template) {
                    make_product_card(all_products, card_template, product_section);
                }
            }
        })
    }

    const single_product_params = new URLSearchParams(window.location.search);
    const single_product_id = single_product_params.get('product_id');
    let single_product = get_product_by_id(single_product_id);
    const single_product_section = document.getElementById('single_product_section');
    if (single_product_section) {
        document.getElementById('product_image').src = `../../../assets/images/product/${single_product.image}`
        document.getElementById('product_name').textContent = single_product.name
        document.getElementById('product_price').textContent = single_product.price + ' €';
        document.getElementById('product_description').textContent = single_product.description;
        document.getElementById('product_code').textContent = single_product.code;
        document.getElementById('product_category').textContent = single_product.category;
        document.getElementById('product_color').textContent = single_product.color;
        document.getElementById('product_brand').textContent = single_product.brand;
        let sizes = Array.isArray(single_product.size) ? single_product.size : [single_product.size];
        let size_options = sizes.map(size => ({ size }));
        fillSelectOptions('product_size_select', size_options, 'size', 'size')
    }

    document.addEventListener('click', (e) => {
        if (e.target.name === 'category_btn') {
            get_product_by_category(e.target.value).then(category => {
                if (category) {
                    if (card_template) {
                        make_product_card(category, card_template, product_section);
                    }
                }
            })
        }
    })
    const form = document.querySelector('.filter_form');
    document.addEventListener('submit', (e) => {
        e.preventDefault();
        if (e.target.classList.contains('filter_form')) {
            apply_filters(e.target).then(products => {
                if (Array.isArray(products) && products.length > 0) {
                    if (card_template) {
                        make_product_card(products, card_template, product_section);
                    }
                } else {
                    product_section.innerHTML = `<div class="alert alert-light text-center w-100">${products}</div>`;
                }
            })
        }
    })



})

async function get_all_products() {
    const cached_products = localStorage.getItem('all_products');
    if (cached_products) {
        return JSON.parse(cached_products);
    }
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/product_controller.php?product');
        let json_res = await response.json();
        if (json_res.status === 'error') return;
        if (json_res.status === 'success') {
            localStorage.setItem('all_products', JSON.stringify(json_res.data));
            return json_res.data;
        }
    } catch (error) {
        console.log(error)
        return null;
    }
}


function get_product_by_id(id) {
    const cached_products = localStorage.getItem('all_products');
    if (cached_products) {
        const products = JSON.parse(cached_products);
        return products.find(product => product.id == id); 
    }
    return null;
}

async function get_product_by_category(category) {
    const category_key = `category_${category}`;
    const cached_category = localStorage.getItem(category_key);
    if (cached_category) {
        return JSON.parse(cached_category);
    }
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/product_controller.php?get_by_category=${category}`);
        let json_res = await response.json();
        if (json_res.status === 'error') return;
        localStorage.setItem(category_key, JSON.stringify(json_res.data));
        return json_res.data;
    } catch (error) {
        console.log(error);
        return null;
    }
}

async function apply_filters(form) {

    const data = JSON.stringify({
        brand: form.brand.value,
        color: form.color.value,
        size: form.size.value,
        material: form.material.value,
        style: form.style.value,
    })
    let options = {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: data
    };
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/filter_controller.php', options);
        let json_res = await response.json();
        return json_res.data;
    } catch (error) {
        console.log(error);
        return null;
    }
}

function make_product_card(array, template, section) {
    const fragment = document.createDocumentFragment();
    array.forEach(produ => {
        template.querySelector('.card_image').src = `../../../assets/images/product/${produ.image}`;
        template.querySelector('.card-title').textContent = produ.name;
        template.querySelector('.card-text').textContent = produ.price + ' €';
        let clone = document.importNode(template, true);
        const product_btn = clone.querySelector('.product_link');
        if (produ.stock <= 0) {
            product_btn.href = "#";
            product_btn.classList.add('out_stock');
            product_btn.textContent = 'Out stock';
            product_btn.addEventListener('click', function(e) { e.preventDefault(); });
        } else {
            product_btn.href = `./single_product.html?product_id=${produ.id}`;
            product_btn.classList.remove('out_stock');
            product_btn.textContent = 'Add cart';
        }
        fragment.appendChild(clone);
    });
    section.innerHTML = "";
    section.appendChild(fragment);
}
