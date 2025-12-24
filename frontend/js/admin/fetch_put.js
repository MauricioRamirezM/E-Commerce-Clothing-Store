
import { validateFormFields, showAlert } from "../../js/admin/functionality.js";
import { loadViews, getSelectOptions } from "../../js/admin/fetch_get_views.js";
const d = document;
d.addEventListener('DOMContentLoaded', function () {

    d.addEventListener("change", (e) => {
        if (e.target.id === "size_type_select") {
            const clothingSelect = document.getElementById('clothing_select');
            const shoesSelect = document.getElementById('shoes_select');
            if (e.target.value === "Clothing Size") {
                if (shoesSelect) shoesSelect.setAttribute('disabled', true);
                if (clothingSelect) clothingSelect.removeAttribute('disabled');
            } else if (e.target.value === "Shoes Size") {
                if (clothingSelect) clothingSelect.setAttribute('disabled', true);
                if (shoesSelect) shoesSelect.removeAttribute('disabled');
            } else {
                if (clothingSelect) clothingSelect.removeAttribute('disabled');
                if (shoesSelect) shoesSelect.removeAttribute('disabled');
            }
        }
    });
    d.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!validateFormFields(e.target)) {
            return;
        }
        if (e.target.id === 'admin_form') {
            updateRegisterAdmin(e.target).then((response) => {
                if (response.status === 'error') {
                    showAlert(false, response.data);
                } else {
                    showAlert(true, response.data);
                    loadViews('admins');
                }
            })
        }
        if (e.target.id === 'client_form') {
            updateRegisterClient(e.target).then((response) => {
                if (response.status === 'error') {
                    showAlert(false, response.data);
                } else {
                    showAlert(true, response.data);
                    loadViews('clients');
                }
            });
        }
        if (e.target.id === 'category_form') {
            updateCreateCategory(e.target).then((response => {
                if (response.status === 'error') {
                    showAlert(false, response.data);
                } else {
                    showAlert(true, response.data);
                    loadViews('categories');
                }
            }))
        }

        if (e.target.id === 'product_form') {
            updateCreateProduct(e.target).then((response) => {
                if (response.status === 'error') {
                    showAlert(false, response.data)
                } else {
                    showAlert(true, response.data)
                    loadViews('products');
                }
            });
        }
        if (e.target.name === 'char_form') {
            updateCreateCharacteristic(e.target).then((response => {
                if (response.status === 'error') {
                    showAlert(false, response.data);
                } else {
                    showAlert(true, response.data);
                    loadViews('characteristics')
                }
            }))
        }
        if (e.target.name === 'pay_country_form') {
            createPayCountry(e.target)
            loadViews('payments')
        }
        if (e.target.name === 'promotion_form') {
            updateCreatePromotion(e.target);
        }
    })
    d.addEventListener('click', async (e) => {

        if (e.target.name === 'delete_admin') {
            if (await deleteElement()) {
                deleteAdmin(e.target);
                loadViews('admins')

            }
        }
        if (e.target.name === 'delete_product') {
            if (await deleteElement()) {
                deleteProduct(e.target);
                loadViews('products')
            }
        }
        if (e.target.name === 'delete_char') {
            if (await deleteElement()) {
                deleteChar(e.target);
                loadViews('characteristics')
            }
        }
        if (e.target.name === 'delete_client') {
            if (await deleteElement()) {
                deleteClient(e.target);
                loadViews('clients')

            }
        }
        if (e.target.name === 'delete_category') {
            if (await deleteElement()) {
                deleteCategory(e.target);
                loadViews('categories')

            }
        }
         if (e.target.name === 'delete_payment_country') {
            if (await deleteElement()) {
                deletePaymentCountry(e.target);
                loadViews('payments')
            }
        }
    });
})



async function updateRegisterAdmin(form) {
    let options = {
        method: "POST",
        headers: { "Content-Type": "application/json; charset=utf-8" },
        body: null
    }
    if (!form.admin_id.value) {
        options.body = JSON.stringify({
            first_name: form.first_name_admin.value,
            last_name: form.last_name_admin.value,
            email: form.email_admin.value,
            phone: form.phone_admin.value,
            password: form.password_admin.value
        })

    } else {

        options.method = "PUT";
        options.body = JSON.stringify({
            id: form.admin_id.value,
            first_name: form.first_name_admin.value,
            last_name: form.last_name_admin.value,
            email: form.email_admin.value,
            phone: form.phone_admin.value,
        })

    };
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/admin_controller.php`, options);
        let res_json = await response.json();
        return res_json;
    } catch (error) {
        console.log(error);
    }

}




async function updateRegisterClient(form) {
    let options = {
        method: "POST",
        headers: { "Content-Type": "application/json; charset=utf-8" },
        body: null
    }
    if (!form.client_id.value) {

        options.body = JSON.stringify({
            first_name: form.first_name_client.value,
            last_name: form.last_name_client.value,
            email: form.email_client.value,
            birthday: form.birthday_client.value,
            phone: form.phone_client.value,
            password: form.password_client.value,
        })
    } else {
        options.method = "PUT";
        options.body = JSON.stringify({
            id: form.client_id.value,
            first_name: form.first_name_client.value,
            last_name: form.last_name_client.value,
            email: form.email_client.value,
            birthday: form.birthday_client.value,
            phone: form.phone_client.value,
        })

    };

    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/clients_controller.php`, options);
        let res_json = await response.json();
        return res_json;
    } catch (error) {
        console.log(error);
    }

}



async function updateCreateCategory(form) {
    let formData = new FormData(form);
    let options = {
        method: "POST",
        body: formData

    };
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/categories_controller.php`, options);
        let res_json = await response.json();
        return res_json;
    } catch (error) {
        console.log(error)
    }
}


async function updateCreateProduct(form) {
    let formData = new FormData(form)
    await getSelectOptions();
    let options = {
        method: "POST",
        body: formData
    };
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/product_controller.php`, options);
        let res_json = await response.json();
        return res_json;
    } catch (error) {
        console.log(error)
    }


}


async function updateCreateCharacteristic(form) {
    let options = {
        method: "POST",
        body: null
    };
    switch (form.id) {
        case 'create_brand_form':
            options.body = JSON.stringify({
                brand_id: form.brand_id.value,
                brand_name: form.brand_name.value,
                brand_description: form.brand_description.value

            })
            break;
        case 'create_color_form':
            options.body = JSON.stringify({
                color_name: form.color_name.value,
            })
            break;
        case 'create_attr_form':
            options.body = JSON.stringify({
                attr_name: form.attribute_name.value,
            })
            break;
        case 'create_subAttr_form':
            options.body = JSON.stringify({
                subAttr_id: form.subAttr_id.value,
                subAttr_name: form.subAttr_name.value,
                attribute_name: form.attr_name.value
            })
            break;
    }

    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php`, options);
        let res_json = await response.json();
        return res_json;
    } catch (error) {
        console.log(error);
    }


}

async function createPayCountry(form) {
    let options = {
        method: 'POST',
        body: null
    };
    switch (form.id) {
        case 'create_payment_form':
            options.body = JSON.stringify({
                payment_name: form.payment_name.value
            })
            break;
        case 'create_country_form':
            options.body = JSON.stringify({
                country_name: form.country_name.value
            })
            break;
    };
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/payment_country_controller.php', options);
        let res_json = await response.json();

        if (res_json.status !== 'success') {
            showAlert(false, res_json.data);
        } else {
            showAlert(true, res_json.data);
        }
    } catch (error) {
        console.log(error);
    }

}


async function deleteAdmin(button) {
    let admin_id = button.dataset.id;
    if (admin_id) {
        try {
            let options = {
                method: "DELETE",
                headers: { "Content-Type": "application/json; charset=utf-8" },
                body: JSON.stringify({
                    admin_id: admin_id
                })
            };

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/admin_controller.php?admin_id=${admin_id}`, options);
            let res_json = await response.json();
            if (res_json.status !== 'success') {
                showAlert(false, res_json.data);
            } else {
                showAlert(true, res_json.data);
            }
        } catch (error) {
            console.log(error)
        }
    }
}

async function deleteClient(button) {
    let client_id = button.dataset.id;
    if (client_id) {
        try {
            let options = {
                method: "DELETE",
                headers: { "Content-Type": "application/json; charset=utf-8" },
                body: JSON.stringify({
                    client_id: client_id
                })

            };

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/clients_controller.php`, options);
            let res_json = await response.json();
            if (res_json.status !== 'success') {
                showAlert(false, res_json.data);
            } else {
                showAlert(true, res_json.data);
            }
        } catch (error) {
            console.log(error)
        }
    }

}
async function deleteProduct(button) {
    let product_id = button.dataset.id;
    if (product_id) {
        try {
            let options = {
                method: "DELETE",
                headers: { "Content-Type": "application/json; charset=utf-8" },
                body: JSON.stringify({
                    product_id: product_id
                })

            };

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/product_controller.php`, options);
            let res_json = await response.json();
            if (res_json.status !== 'success') {
                showAlert(false, res_json.data);
            } else {
                showAlert(true, res_json.data);
            }
        } catch (error) {
            console.log(error)
        }
    }
}
async function deleteCategory(button) {
    let category_id = button.dataset.id;
    if (category_id) {
        try {
            let options = {
                method: "DELETE",
                headers: { "Content-Type": "application/json; charset=utf-8" },
                body: JSON.stringify({
                    category_id: category_id
                })

            };

            let response = await fetch(`http://localhost/e-commerce/backend/controllers/categories_controller.php`, options);
            let res_json = await response.json();
            if (res_json.status !== 'success') {
                showAlert(false, res_json.data);
            } else {
                showAlert(true, res_json.data);

            }
        } catch (error) {
            console.log(error)
        }
    }
}
async function deleteChar(button) {
    let char_id = button.dataset.id;
    let options = {
        method: "DELETE",
        headers: { "Content-Type": "application/json; charset=utf-8" },
        body: null
    }
    switch (button.value) {
        case 'delete_brand':
            options.body = JSON.stringify({
                brand_id: char_id
            })
            break;
        case 'delete_color':
            options.body = JSON.stringify({
                color_id: char_id
            })
            break;
        case 'delete_attribute':
            options.body = JSON.stringify({
                attr_id: char_id
            })
            break;
        case 'delete_subAttr':
            options.body = JSON.stringify({
                subAttr_id: char_id
            })
            break;
    }
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/size_brand_color_attr_Controller.php', options);
        let res_json = await response.json();
        if (res_json.status !== 'success') {
            showAlert(false, res_json.data);
        } else {
            showAlert(true, res_json.data);
        }
    } catch (error) {
        console.log(error)
    }

}
async function deletePaymentCountry(button) {
    let char_id = button.dataset.id;
    let options = {
        method: "DELETE",
        headers: { "Content-Type": "application/json; charset=utf-8" },
        body: null
    }
    switch (button.value) {
        case 'delete_payment':
            options.body = JSON.stringify({
                payment_id: char_id
            })
            break;
        case 'delete_country':
            options.body = JSON.stringify({
                country_id: char_id
            })
            break;
    }
    try {
        let response = await fetch('http://localhost/e-commerce/backend/controllers/payment_country_controller.php', options);
        let res_json = await response.json();
        if (res_json.status !== 'success') {
            showAlert(false, res_json.data);
        } else {
            showAlert(true, res_json.data);
        }
    } catch (error) {
        console.log(error)
    }

}




async function updateCreatePromotion(form) {
    let formData = new FormData(form);
    let options = {
        method: "POST",
        body: formData
    };
    try {
        let response = await fetch(`http://localhost/e-commerce/backend/controllers/promotion_controller.php`, options);
        let res_json = await response.json();
        if (res_json.status !== 'success') {
            showAlert(false, res_json.data);
        } else {
            showAlert(true, res_json.data);
        }
    } catch (error) {
        console.log(error);
    }
}

function deleteElement() {
    return new Promise((resolve) => {
        // Ensure Bootstrap Modal is available
        const modalEl = document.getElementById('confirmDeleteModal');
        if (!modalEl) {
            // fallback to native confirm if modal not found
            resolve(confirm('Are you sure you want to delete this element?'));
            return;
        }
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const cancelBtn = document.getElementById('cancelDeleteBtn');
        let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        confirmBtn.onclick = null;
        cancelBtn.onclick = null;

        confirmBtn.onclick = function () {
            bsModal.hide();
            resolve(true);
        };
        cancelBtn.onclick = function () {
            bsModal.hide();
            resolve(false);
        };
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
        });
        bsModal.show();
    });
}




