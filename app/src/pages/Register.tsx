//components
import { RegisterForm } from '../components/InputForm'
import { useEffect } from 'react';
import Swal from 'sweetalert2';

const RegisterPage = () => {
    useEffect(() => {
        Swal.fire({
            icon: "warning",
            title: "WARNING",
            text: "This is a demo project. For your security, DO NOT use your real email or a password you use elsewhere. Use fake data (e.g., user@test.com)."
        });
    }, []); //Init website.

    return (
        <div className={`min-h-screen justify-center items-center flex bg-gray-800`} >
            <RegisterForm />
        </div>
    )
}//Entire register page.

export default RegisterPage