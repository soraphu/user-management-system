//components
import { swalConfirmButtonColor } from '@/helper/config';
import RegisterForm from '../components/register-form'
import { useEffect } from 'react';
import Swal from 'sweetalert2';
import { useUserAction } from '@/helper/handleAccessUser';

const RegisterPage = () => {
    const { ensureLoggedIn } = useUserAction();

    useEffect(() => {
        const initState = async () => {
            const isNavigateHome = await ensureLoggedIn();

            if (isNavigateHome) return;

            Swal.fire({
                icon: "warning",
                title: "WARNING",
                text: "This is a demo project. For your security, DO NOT use your real email or a password you use elsewhere. Use fake data (e.g., user@test.com).",
                confirmButtonColor: swalConfirmButtonColor,
                confirmButtonText: "I Understand"
            });
        }

        initState();
    }, []); //Init website.


    return (
        <div className={`min-h-screen justify-center items-center flex bg-gray-800`} >
            <RegisterForm />
        </div>
    )
}//Entire register page.

export default RegisterPage