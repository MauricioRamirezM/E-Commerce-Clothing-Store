

document.addEventListener('DOMContentLoaded', ()=>{
    const profile_container = document.getElementById( 'profile_container');
    if(profile_container){
        let user_orders = JSON.parse(localStorage.getItem('user_orders'));
        let user_addresses = JSON.parse(localStorage.getItem('user_addresses'));
        let user_info  = JSON.parse(localStorage.getItem('user_info'));
       if(user_orders && user_addresses && user_info){
        displayProfileInfo(user_info, user_addresses, user_orders);
       }
    }
})


function displayProfileInfo(user, address, orders) {
    const client_info = user;
    const client_address = address;
    const client_orders = orders;
    let fullName = client_info.first_name + " " + client_info.last_name;
    const fullDate = client_info.created_at;
    let dateOnly = fullDate.split(' ')[0];

    document.getElementById('profile_name').textContent = fullName;
    document.getElementById('profile_email').href = "mailto:" + client_info.email;
    document.getElementById('profile_email').textContent = client_info.email;
    document.getElementById('profile_user_number').textContent = client_info.user_id;
    document.getElementById('profile_phone').textContent = client_info.phone;
    document.getElementById('profile_creation').textContent = dateOnly;
    document.getElementById('missing_points_span').textContent = client_info.points


    document.getElementById('profile_address_table').innerHTML = "";
    document.getElementById('profile_orders_table').innerHTML = "";
    const productDetailsTable = document.querySelector('.prduct_details_table');
    if (productDetailsTable) productDetailsTable.innerHTML = "";

    if (!Array.isArray(client_address) || client_address.length === 0) {
        let address_row = `
            <tr>
                 <td colspan="9"><h4>No address to show</h4></td>
            </tr>
        `;
        document.getElementById('profile_address_table').insertAdjacentHTML('beforeend', address_row);
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
            document.getElementById('profile_address_table').insertAdjacentHTML('beforeend', address_row);
        });
    }

    if (!Array.isArray(client_orders) || client_orders.length === 0) {
        let order_row = `
            <tr class="">
                <td colspan="7"><h4>No orders to show.</h4></td>
            </tr>
        `;
        document.getElementById('profile_orders_table').insertAdjacentHTML("beforeend", order_row);
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
            document.getElementById('profile_orders_table').insertAdjacentHTML("beforeend", order_row);
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
    const productDetailsTable = document.getElementById('product_details_table');
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
    document.getElementById('total_order').textContent = totalOrder + '€';
 
    if (order.address) {
        const parts = order.address.split(',');
        document.getElementById('shipping_name').textContent = fullName;
        document.getElementById('shippnig_street_num').textContent = parts[0] ? parts[0].trim() : "";
        document.getElementById('shipping_city_state_cp').textContent = parts.slice(1, 4).map(p => p.trim()).join(', ');
        document.getElementById('shipping_country').textContent = parts[4] ? parts[4].trim() : "";
        document.getElementById('shipping_phone').textContent = client_info.phone ? `Phone: ${client_info.phone}` : "";
    }
    if (order.payment_method) {
        const methodSpan = document.getElementById('method_span');
        if (methodSpan) methodSpan.textContent = order.payment_method;
    }
    const detailsSection = document.getElementById('product_details_section');
    detailsSection.classList.remove('hide_ele');
    detailsSection.classList.add('show_ele');

    if (detailsSection) {

        detailsSection.scrollIntoView({ behavior: 'smooth' });
    }
}


