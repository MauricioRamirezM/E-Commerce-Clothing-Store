import { showAlert } from "../../js/admin/functionality.js";

document.addEventListener('DOMContentLoaded', (e)=>{
    
    document.addEventListener('submit', (e)=>{
        e.preventDefault();
        const admin_form = document.getElementById('login_admin_form');
        
    if(admin_form){
        authenticate(e.target, 'login_admin').then((response)=>{
            if(response.status === 'error'){
                showAlert(false, response.message)
            }else{
                showAlert(true, response.message);
                setTimeout(()=>{
                    window.location.href= 'http://localhost/e-commerce/frontend/html/admin/index.html';
                }, 3000)
            }
        })
    }
    })
    document.addEventListener('click', (e)=>{
        if(e.target.name === 'admin_logout_btn'){
            user_logout().then((response)=>{
                if(response.status === 'error'){
                    showAlert(false, response.data)
                }else{
                    showAlert(true, response.data)
                    setTimeout(()=>{
                        window.location.href = 'http://localhost/e-commerce/frontend/html/admin/login.html'
                }, 3000)
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

