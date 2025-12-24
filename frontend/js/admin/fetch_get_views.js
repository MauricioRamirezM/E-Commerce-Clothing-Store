const d = document;
const view_conte = d.getElementById('view_conte');



d.addEventListener("DOMContentLoaded", (e) => {
    getViews("");


    d.addEventListener('click', (e) => {
        if (e.target.closest("#home_btn")) {
            getViews("");
        }
        if (e.target.matches('.menu_btn')) {
            loadViews(e.target.value);
        }
        if (e.target.matches('.add_btn')) {
            getCreateViews(e.target.value);
        }
        if (e.target.matches('#update_btn')) {

            get_update_views(e.target.value).then(() => {
                if (e.target.value === 'update_admin') getAdminInfo(e.target);
                if (e.target.value === 'update_client') {
                    getClientInfo(e.target).then(clientInfo => {
                        fillUpdateClientForm(clientInfo);
                    });
                }
                if (e.target.value === 'update_product') getproductInfo(e.target);
                if (e.target.value === 'update_category') getCategoryInfo(e.target);
            })
        }

        if (e.target.id === "profile_client_btn") {
            getViews(e.target.value).then(() => {
                getClientInfo(e.target).then(clientInfo => {
                    displayProfileInfo(clientInfo);
                });
            });
        }
        if (e.target.matches('#update_char_btn')) {
            if (e.target.value === 'update_brand') getBrandInfo(e.target);
            if (e.target.value === 'update_color') getColorInfo(e.target);
            if (e.target.value === 'update_attribute') getAttributeInfo(e.target);
            if (e.target.value === 'update_subAttribute') getSubAttributeInfo(e.target);
            if (e.target.value === 'update_promotion') getPromosInfo(e.target);

        }


        if (e.target.matches('.cancel_form_btn')) {
            const form = e.target.closest('form');
            if (form) form.reset();

            loadViews(e.target.value)
        }




    })






})

export function loadViews(target) {
    getViews(target).then(() => {
        if (target === 'clients') listClients();
        if (target === 'admins') listAdmins();
        if (target === 'products') listProducts();
        if (target === 'categories') listCategories();
        if (target === 'characteristics') listCharacteristics();
        if (target === 'promotions') listPromos();
        if (target === 'payments') listCountryPayment();
    });
}



async function getViews(view) {

    try {
        if (view === "") {
            view = 'home';
        }

        let response = await fetch(`http://localhost/e-commerce/backend/controllers/views_controller.php?page=${view}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );
        let page = await response.text();
        try {
            let json = JSON.parse(page);
            if (json.redirect) {
                window.location.href = json.redirect;
                return;
            }
        } catch (e) {
        }
        view_conte.innerHTML = page;

    } catch (error) {
        console.log(error);


    }
}
async function getCreateViews(view) {
    try {
        if (view != "") {

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/views_controller.php?create=${view}`
            );
            let page = await response.text();
            view_conte.innerHTML = page;
            setTimeout(() => {
                getSelectOptions(); 
            }, 50);
        }
    } catch (error) {
        console.log(error);


    }
}


async function get_update_views(view) {
    try {
        if (view != "") {

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/views_controller.php?update=${view}`);
            let page = await response.text();
            view_conte.innerHTML = page;

        }
    } catch (error) {
        console.log(error);


    }

}
async function listProducts() {
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/product_controller.php?product=1');
        let products = await response.json();
        if (products.status == "success") {
            const product_table = d.getElementById('product_table');
            product_table.innerHTML = "";

            products.data.forEach(ele => {
                const table_row = `
                                 <tr>
                    <td>${ele.id} </td>
                    <td>${ele.code} </td>
                    <td>
                    <img style="max-width: 4rem;" src="../../../assets/images/product/${ele.image}" alt="">
                    </td>
                    <td>${ele.name} </td>
                    <td title="${ele.description}">${ele.description.slice(0, 30)}${ele.description.length > 30 ? '...' : ''}</td>
                    <td>${ele.brand} </td>
                    <td>${ele.category}</td>
                    <td>${ele.color}</td>
                    <td>${ele.size} </td>
                    <td>${ele.price}</td>
                    <td>${ele.stock}</td>
                     <td>
                     
                        <button value="delete_product" name="delete_product" data-id="${ele.id}" id="delete_btn" class="btn red_btn" >Delete</button>
                        <button value="update_product" name="update_btn" id="update_btn" class="btn green_btn" ">Update</button>
                    </td>

                    </tr>
                `;
                product_table.insertAdjacentHTML('beforeend', table_row);
            });
        }
    } catch (error) {
        console.log(error);
    }

}
async function listAdmins() {


    try {

        let response = await fetch(`http://localhost/e-commerce/backend/controllers/admin_controller.php?admin`);
        let admin = await response.json();
        if (admin.status == "success") {
            const admin_table = d.getElementById('admins_table');
            admin_table.innerHTML = "";

            admin.data.forEach(ele => {
                const table_row = `
                                 <tr>
                    <td id="user_id" >${ele.id} </td>
                    <td>${ele.first_name + ' ' + ele.last_name} </td>
                    <td>${ele.email} </td>
                    <td>${ele.phone} </td>
                     <td>
                        <button name="delete_admin"  id="delete_btn" data-id="${ele.id}" class="btn red_btn">Delete</button>
                        <button value="update_admin" id="update_btn"  name="update_btn"  class="btn green_btn" ">Update</button>
                    </td>

                    </tr>
                `;
                admin_table.insertAdjacentHTML('beforeend', table_row);
            });
        }
    } catch (error) {
        console.log(error);
    }
}
async function listClients() {


    try {

        let response = await fetch(`http://localhost/e-commerce/backend/controllers/clients_controller.php?client=1`);
        let clients = await response.json();
        if (clients.status == "success") {
            const clients_table = d.getElementById('clients_table');
            clients_table.innerHTML = "";


            clients.data.forEach(ele => {
                const table_row = `
                                 <tr>
                    <td id="user_id" >${ele.id} </td>
                    <td>${ele.first_name + ' ' + ele.last_name} </td>
                    <td>${ele.email} </td>
                    <td>${ele.phone} </td>
                     <td>
                     <div class="dropdown justify-content-center">
                     <button class="btn dark_btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                     Options
                     </button>
                     <ul class="dropdown-menu justify-content-center ">
                            <li calss="li_option_menu"><button value="update_client" id="update_btn" data-id="${ele.id}" name="update_btn"  class="  option_menu_btn btn btn-sm green_btn" ">Update</button></li>
                            <li calss="li_option_menu"><button value="profile" id="profile_client_btn" data-id="${ele.id}" name="inspect_btn"  class=" option_menu_btn btn btn-sm blue_btn" ">Inspect</button></li>
                            <li calss="li_option_menu"> <button name="delete_client"  id="delete_btn" data-id="${ele.id}"  class="btn option_menu_btn  btn-sm red_btn">Delete</button></li>
                           
                        </ul>
                        </div>
                        
                    </td>

                    </tr>
                `;
                clients_table.insertAdjacentHTML('beforeend', table_row);
            });
        }
    } catch (error) {
        console.log(error);
    }
}

async function listCategories() {
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/categories_controller.php?catego=1`);
        let categories = await response.json();
        if (categories.status == 'success') {
            const catego_table = d.getElementById('category_table');
            catego_table.innerHTML = "";
            categories.data.forEach(ele => {
                const table_row = ` 
                <tr>
                    <td>${ele.category_id}</td>
                    <td>${ele.category_name}</td>
                    <td>${ele.category_description}</td>
                    <td>${ele.size}</td>
                    <td>
                        <button value="delete_category" name="delete_category" data-id="${ele.category_id}" id="delete_btn" class="btn red_btn btn_options" ><small>Delete</small></button>
                        <button value="update_category" name="update_btn" id="update_btn" class="btn green_btn btn_options" >Update</button>
                    </td>
                </tr>`;
                catego_table.insertAdjacentHTML('beforeend', table_row);
            })




        }
    } catch (error) {
        console.log(error);
    }
}

async function listCharacteristics() {
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?characteristics=1`);
        let characteri = await response.json();
        if (characteri.status == 'success') {
            const brand_table = d.querySelector('#brand_table');
            const color_table = d.getElementById('color_table');
            const attr_table = d.getElementById('attribute_table');
            const subAttr_table = d.getElementById('subAttribute_table');
            const clothing_span = d.getElementById("clothing_span_sizes");
            const shoes_span = d.getElementById("shoes_span_sizes");
            clothing_span.textContent = ""
            shoes_span.textContent = "";
            brand_table.innerHTML = "";
            color_table.innerHTML = "";
            attr_table.innerHTML = "";
            brand_table.innerHTML = "";

            let cloth_sizes = [];
            characteri.data.clothing_sizes.forEach(ele => {
                cloth_sizes.push(ele.size);
            })
            clothing_span.textContent = cloth_sizes.join(', ');

            let shoe_sizes = [];
            characteri.data.shoes_sizes.forEach(ele => {
                shoe_sizes.push(ele.size);
            })
            shoes_span.textContent = shoe_sizes.join(', ');

            characteri.data.brands.forEach(ele => {
                const brand_row = ` 
                <tr>
                    <td>${ele.brand_id}</td>
                    <td>${ele.brand_name}</td>
                    <td id="td_description_body" >${ele.brand_description}</td>
                    <td>
                        <button value="delete_brand" name="delete_char" data-id="${ele.brand_id}" id="delete_btn" class="btn red_btn btn_options" ><small>Delete</small></button>
                        <button value="update_brand" name="update_btn" id="update_char_btn" class="btn green_btn btn_options">Update</button>
                    </td>
                </tr>`;
                brand_table.insertAdjacentHTML('beforeend', brand_row);

            })
            characteri.data.colors.forEach(ele => {
                const color_row = `
                    <tr>
                    <td>${ele.color_id}</td>
                    <td>${ele.color_name}</td>
                    <td>
                        <button value="delete_color" name="delete_char" data-id="${ele.color_id}" id="delete_btn" class="btn red_btn btn_options" href="#" role="button"><small>Delete</small></button>
                       
                    </td>
                </tr>
                `;
                color_table.insertAdjacentHTML('beforeend', color_row);

            })
            characteri.data.attributes.forEach(ele => {
                const attr_row = ` 
                <tr>
                    <td>${ele.attribute_name}</td>
                    <td>
                        <button value="delete_attribute" name="delete_char" id="delete_btn" data-id="${ele.attribute_type_id}" class="btn red_btn btn_options" href="#" role="button"><small>Delete</small></a>
                    </td>
                </tr>`;
                attr_table.insertAdjacentHTML('beforeend', attr_row);


            })
            characteri.data.sub_attr.forEach(ele => {

                const subAttr_row = ` 
                <tr>
                    <td>${ele.id}</td>
                    <td>${ele.name}</td>
                    <td>${ele.attribute}</td>
                    <td>
                        <button value="delete_subAttr" name="delete_char" id="delete_btn" data-id="${ele.id}"  class="btn red_btn btn_options""><small>Delete</small></button>
                        <button value="update_subAttribute" name="update_btn" id="update_char_btn" class="btn green_btn btn_options">Update</button>
                    </td>
                </tr>`;
                subAttr_table.insertAdjacentHTML('beforeend', subAttr_row);
                fillSelectOptions("subAttr_select", characteri.data.attributes, 'attribute_name', 'attribute_name');
            })




        }
    } catch (error) {
        console.log(error);
    }
}

async function listPromos() {
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/promotion_controller.php?promo=1`);
        let promotions = await response.json();
        if (promotions.status == 'success') {
            const promo_table = d.getElementById('promo_table');
            promo_table.innerHTML = "";
            promotions.data.forEach(ele => {
                const table_row = ` 
                <tr>
                    <td>${ele.promo_id}</td>
                    <td>${ele.promo_name}</td>
                    <td>${ele.promo_description}</td>
                    <td>${ele.discount_porcent}</td>
                    <td>${ele.start_date}</td>
                    <td>${ele.end_date}</td>
                    <td>
                        <button value="delete_promotion" name="delete_promotion" id="delete_btn" data-id="${ele.promo_id}"  class="btn red_btn btn_options"><small>Delete</small></button>
                        <button value="update_promotion" name="update_btn" id="update_char_btn" class="btn green_btn btn_options" >Update</button>
                    </td>
                </tr>`;
                promo_table.insertAdjacentHTML('beforeend', table_row);
            })




        }
    } catch (error) {
        console.log(error);
    }
}
async function listCountryPayment() {
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/payment_country_controller.php?payCon=1`);
        let payCoun = await response.json();
        if (payCoun.status == 'success') {
            const payment_table = d.getElementById('payment_table');
            const country_table = d.getElementById('country_table');
            payCoun.data.payment.forEach(ele => {
                const payment_row = ` 
                <tr>
                    <td>${ele.payment_type_name}</td>
                    <td>
                        <button value="delete_payment" name="delete_payment_country" data-id="${ele.payment_type_id}"  id="delete_btn" class="btn red_btn btn_options">Delete</button>
                    </td>
                </tr>`;
                payment_table.insertAdjacentHTML('beforeend', payment_row);

            })
            payCoun.data.country.forEach(ele => {
                const country_row = ` 
                <tr>
                    <td>${ele.country_name}</td>
                    <td>
                        <button value="delete_country" name="delete_payment_country" id="delete_btn" data-id="${ele.country_id}"  class="btn red_btn btn_options"  role="button">Delete</button>
                    </td>
                </tr>`;
                country_table.insertAdjacentHTML('beforeend', country_row);

            })




        }
    } catch (error) {
        console.log(error);
    }
}

async function getAdminInfo(button) {
    let admin_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/admin_controller.php?getAdmin=${admin_id}`);
        let adminInfo = await response.json();
        if (adminInfo.status == 'success') {
            const id_admin = d.getElementById('admin_id');
            const first_name = d.getElementById('first_name_admin');
            const last_name = d.getElementById('last_name_admin');
            const email = d.getElementById('email_admin');
            const phone = d.getElementById('phone_admin')
            id_admin.value = adminInfo.data.admin_id;
            first_name.value = adminInfo.data.first_name;
            last_name.value = adminInfo.data.last_name;
            email.value = adminInfo.data.email;
            phone.value = adminInfo.data.phone;

        }

    } catch (error) {
        console.log(error)
    }

}
async function getClientInfo(button) {
    let client_id = button.dataset.id;

    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/clients_controller.php?getClient=${client_id}`);
        let clientInfo = await response.json();
        return clientInfo;

    } catch (error) {
        console.log(error)
    }

}
function fillUpdateClientForm(obj) {
    if (obj.status == 'success') {
        const id_client = d.getElementById('client_id');
        const first_name = d.getElementById('first_name_client');
        const last_name = d.getElementById('last_name_client');
        const email = d.getElementById('email_client');
        const birthday = d.getElementById('birthday_client');
        const phone = d.getElementById('phone_client');
        id_client.value = obj.data.clientInfo.user_id;
        first_name.value = obj.data.clientInfo.first_name;
        last_name.value = obj.data.clientInfo.last_name;
        email.value = obj.data.clientInfo.email;
        birthday.value = obj.data.clientInfo.birthday_date;
        phone.value = obj.data.clientInfo.phone;

    }
}


function displayProfileInfo(obj) {
    if (obj.status !== 'success') {
        return;
    }
    const client_info = obj.data.clientInfo;
    const client_address = obj.data.clientAddresses;
    const client_orders = obj.data.clientOrders;
    let fullName = client_info.first_name + " " + client_info.last_name;
    const fullDate = client_info.created_at;
    let dateOnly = fullDate.split(' ')[0];

    d.getElementById('profile_name').textContent = fullName;
    d.getElementById('profile_email').href = "mailto:" + client_info.email;
    d.getElementById('profile_email').textContent = client_info.email;
    d.getElementById('profile_user_number').textContent = client_info.user_id;
    d.getElementById('profile_phone').textContent = client_info.phone;
    d.getElementById('profile_creation').textContent = dateOnly;
    d.getElementById('missin_ponits_span').textContent = client_info.points


    d.getElementById('profile_address_table').innerHTML = "";
    d.getElementById('profile_orders_table').innerHTML = "";
    const productDetailsTable = d.querySelector('.prduct_details_table');
    if (productDetailsTable) productDetailsTable.innerHTML = "";

    if (!client_address || client_address.length === 0) {
        let address_row = `
            <tr>
                 <td colspan="9"><h4>No address to show</h4></td>
            </tr>
        `;
        d.getElementById('profile_address_table').insertAdjacentHTML('beforeend', address_row);
    } else {
        client_address.forEach((ele) => {
            let intNumber = ele.int_number ? ele.int_number : "";
            let IsDefault = ele.is_default ? "YES" : "NOT";
            let address_row = `
                <tr>
                    <td>${ele.address_id}</td>
                    <td>${ele.ext_number}</td>
                    <td>${intNumber}</td>
                    <td>${ele.city}</td>
                    <td>${ele.state}</td>
                    <td>${ele.street}</td>
                    <td>${ele.cp}</td>
                    <td>${ele.country_name}</td>
                    <td>${IsDefault}</td>
                </tr>
            `;
            d.getElementById('profile_address_table').insertAdjacentHTML('beforeend', address_row);
        });
    }

    if (!client_orders || client_orders.length === 0) {

        let order_row = `
            <tr class="">
                <td colspan="7"><h4>No orders to show.</h4></td>
            </tr>
        `;
        d.getElementById('profile_orders_table').insertAdjacentHTML("beforeend", order_row);
    } else {
        client_orders.forEach((order, i) => {

            let order_row = `
                <tr class="">
                    <td>${order.order_number}</td>
                    <td>${order.status}</td>
                    <td>${order.payment_method}</td>
                    <td>${order.address}</td>
                    <td>${order.date ? order.date.split(' ')[0] : ''}</td>
                    <td>${order.total} €</td>
                    <td>
                        <button type="button" class="btn btn-primary blue_btn order_details_btn" data-order-index="${i}">
                            Details
                        </button>
                    </td>
                </tr>
            `;
            d.getElementById('profile_orders_table').insertAdjacentHTML("beforeend", order_row);
        });

        document.querySelectorAll('.order_details_btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const orderIndex = this.getAttribute('data-order-index');
                showOrderDetails(client_orders[orderIndex], client_info, fullName);
            });
        });
    }

}

function showOrderDetails(order, client_info, fullName) {
    const productDetailsTable = document.querySelector('.prduct_details_table');
    if (productDetailsTable) productDetailsTable.innerHTML = "";
    let totalOrder = 0;

    if (order.products && Array.isArray(order.products)) {

        order.products.forEach((product) => {

            let subtotal = product.price * product.quantity;
            totalOrder += subtotal;
            const product_details_row = `
                <tr class="">
                    <td class="td_product_details ">
                        <div class="row">
                            <div class="col-sm-2">
                                <img style="width: 80px;"
                                    id="product_details_img"
                                    src="../../../assets/images/product/${product.img_filename}"
                                    alt="${product.img_filename}">
                            </div>
                            <div class="col-sm-10 text-start">
                                <p id="td_product_name">${product.product_name}</p>
                                <p id="td_price_quantity" class="">Price:<span
                                        class="td_price me-2">${product.price}</span> | Quantity: <span
                                        class=" td_quantity"> ${product.quantity}</span></p>
                            </div>
                        </div>
                    </td>
                    <td class="td_subtotal_details text-center">${subtotal}</td>
                </tr>
            `;
            productDetailsTable.insertAdjacentHTML('beforeend', product_details_row);
        });
    }
    d.getElementById('total_order').textContent = totalOrder + '€';

    if (order.address) {
        const parts = order.address.split(',');
        d.getElementById('shipping_name').textContent = fullName;
        d.getElementById('shippnig_street_num').textContent = parts[0] ? parts[0].trim() : "";
        d.getElementById('shipping_city_state_cp').textContent = parts.slice(1, 4).map(p => p.trim()).join(', ');
        d.getElementById('shipping_country').textContent = parts[4] ? parts[4].trim() : "";
        d.getElementById('shipping_phone').textContent = client_info.phone ? `Phone: ${client_info.phone}` : "";
    }
    if (order.payment_method) {
        const methodSpan = d.getElementById('method_span');
        if (methodSpan) methodSpan.textContent = order.payment_method;
    }
    const detailsSection = document.getElementById('product_details_section');
    detailsSection.classList.remove('hide_ele');
    detailsSection.classList.add('show_ele');

    if (detailsSection) {

        detailsSection.scrollIntoView({ behavior: 'smooth' });
    }
}





async function getproductInfo(button) {
    try {
        let produ_id = button.parentNode.parentNode.firstElementChild.textContent;
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/product_controller.php?getProduct=${produ_id}`);
        let product_info = await response.json();

        if (product_info) {
            await getSelectOptions();
            d.getElementById('update_product_image').src = `../../../assets/images/product/${product_info.data.image}`;
            d.getElementById('product_id').value = product_info.data.id;
            d.getElementById('product_code').value = product_info.data.code;
            d.getElementById('product_stock').value = product_info.data.stock;
            d.getElementById('product_name').value = product_info.data.name;
            d.getElementById('product_description').value = product_info.data.description;
            d.getElementById('product_brand').value = product_info.data.brand;
            d.getElementById('product_price').value = product_info.data.price;
            d.getElementById('product_sale_price').value = product_info.data.sale_price;

            const product_category = d.querySelector('#product_category');
            const product_color = d.getElementById('product_color');
            const size_type = d.getElementById('size_type_select');
            const product_attribute = d.getElementById('product_type_attribute');
            const product_subAttribute = d.getElementById('product_subtype_attribute');
            const shoes_select = d.getElementById('shoes_select');
            const clothing_select = d.getElementById('clothing_select');
            fillSelect(product_category, product_info.data.category)
            fillSelect(product_color, product_info.data.color)
            fillSelect(size_type, product_info.data.sizeCategory)

            if (product_info.data.sizeCategory == 'Clothing Sizes') {
                shoes_select.setAttribute('disabled', true)
                fillSelect(clothing_select, product_info.data.size)
            } else if (product_info.data.sizeCategory == 'Shoe Sizes') {
                clothing_select.setAttribute('disabled', true);
                fillSelect(shoes_select, product_info.data.size);
            }

            fillSelect(product_attribute, product_info.data.attribute);
            fillSelect(product_subAttribute, product_info.data.subAttribute);
        }
    } catch (error) {
        console.log(error);
    }

}

async function getCategoryInfo(button) {
    let category_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/categories_controller.php?getCatego=${category_id}`);
        let catego_info = await response.json();
        if (catego_info.status == 'success') {
            const category_id = d.getElementById('category_id').value = catego_info.data.category_id;
            const category_name = d.getElementById('category_name').value = catego_info.data.category_name;
            const category_description = d.getElementById('category_description').value = catego_info.data.category_description;
            const category_size = d.getElementById('category_size');
            const category_parent = d.getElementById('category_parent');
            fillSelect(category_size, catego_info.data.size_category_id)
            fillSelect(category_parent, catego_info.data.parent_category_id)
        }
    } catch (error) {
        console.log(error);
    }
}


async function getBrandInfo(button) {
    let brand_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?getBrand=${brand_id}`);
        let brand_info = await response.json();
        if (brand_info) {
            const update_button = d.getElementById('update_brand_btn');
            const add_button = d.getElementById('add_brand_id');
            update_button.classList.remove('hide')
            add_button.classList.add('hide')
            d.getElementById('brand_id').value = brand_info.data.brand_id;
            d.getElementById('brand_name').value = brand_info.data.brand_name;
            d.getElementById('brand_description').value = brand_info.data.brand_description;
        }
    } catch (error) {
        console.log(error);
    }
}
async function getColorInfo(button) {
    let color_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?getColor=${color_id}`);
        let color_info = await response.json();
        if (color_info) {
            d.getElementById('color_id').value = color_info.data.color_id;
            d.getElementById('color_name').value = color_info.data.color_name;
        }
    } catch (error) {
        console.log(error);
    }
}
async function getAttributeInfo(button) {
    let attr_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?getAttr=${attr_id}`);
        let attr_info = await response.json();
        if (attr_info) {
            d.getElementById('attribute_id').value = attr_info.data.attribute_type_id;
            d.getElementById('attribute_name').value = attr_info.data.attribute_name;
        }
    } catch (error) {
        console.log(error);
    }
}
async function getSubAttributeInfo(button) {
    let subAttr_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php?getSubAttr=${subAttr_id}`);
        let subAttr_info = await response.json();
        if (subAttr_info) {
            const update_button = d.getElementById('update_subAttr_btn');
            const add_button = d.getElementById('add_subAttr_id');
            const subAttr_select = d.getElementById('subAttr_select');
            update_button.classList.remove('hide')
            add_button.classList.add('hide');
            d.getElementById('subAttr_id').value = subAttr_info.data.attribute_option_id;
            d.getElementById('subAttr_name').value = subAttr_info.data.attribute_option_name;
            fillSelect(subAttr_select, subAttr_info.data.attribute_name)
        }
    } catch (error) {
        console.log(error);
    }
}

async function getPromosInfo(button) {
    let promo_id = button.parentNode.parentNode.firstElementChild.textContent;
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/promotion_controller.php?getPromo=${promo_id}`);
        let promo_info = await response.json();
        if (promo_info) {
            const update_button = d.getElementById('update_promo_btn');
            const add_button = d.getElementById('add_promo_id')
            update_button.classList.remove('hide')
            add_button.classList.add('hide')
            d.getElementById('promo_id').value = promo_info.data.promo_id;
            d.getElementById('promo_name').value = promo_info.data.promo_name;
            d.getElementById('promo_description').value = promo_info.data.promo_description;
            d.getElementById('promo_porcent').value = promo_info.data.discount_porcent;
            d.getElementById('promo_start_date').value = promo_info.data.start_date;
            d.getElementById('promo_end_date').value = promo_info.data.end_date;
        }
    } catch (error) {
        console.log(error);
    }
}





function fillSelect(select, value) {
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value == value) {
            select.selectedIndex = i;
            return;
        }
    };
}



export async function getSelectOptions() {
    try {
        const res = await fetch("http://localhost/e-commerce/backend/controllers/filter_controller.php");
        const json_res = await res.json();
        if (json_res.status === "success") {
            const data = json_res.data;
            const attributes = data.attrs;
            const groupedAttrs = {};
            attributes.forEach(attr => {
                const group = attr.attr_name;
                if (!groupedAttrs[group]) {
                    groupedAttrs[group] = [];
                }
                groupedAttrs[group].push(attr);
            });

            fillSelectOptions("product_category", data.categories, "category_name", "category_name");
            fillSelectOptions("product_color", groupedAttrs['Color Group'], "subAttr_name", "subAttr_name");
            fillSelectOptions("clothing_select", data.clothing_sizes, "size", "size");
            fillSelectOptions("shoes_select", data.shoes_sizes, "size", "size");
            const uniqueAttrNames = [];
            const seen = new Set();
            attributes.forEach(attr => {
                if (!seen.has(attr.attr_name)) {
                    uniqueAttrNames.push({ attr_name: attr.attr_name });
                    seen.add(attr.attr_name);
                }
            });
            fillSelectOptions("product_type_attribute", uniqueAttrNames, "attr_name", "attr_name");
            fillSelectOptions("product_subtype_attribute", attributes, "subAttr_name", "subAttr_name");
        }
    } catch (error) {
        console.error("Error loading attributes:", error);
    }
}

function fillSelectOptions(selectEle, options, value, text) {
    const select = d.getElementById(selectEle);
    if (!select) return;
    select.length = 1;
    options.forEach(opt => {
        const option = document.createElement("option");
        option.value = opt[value];
        option.textContent = opt[text];
        select.appendChild(option);
    });
}






