import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { Toaster } from 'sonner';
import './App.css'

//Page imports
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import MockMail from './pages/MockMail';
import VerifyEmailRequest from './pages/VerifyEmailRequest';
import ResetPasswordPage from './pages/ResetPassword';
import ForgetPasswordPage from './pages/ForgetPassword';
import DashboardPage from './pages/Dashboard';
import PageNotFound from './pages/PageNotFound';
import VerifiedEmail from './pages/VerifiedEmail';

const router = createBrowserRouter([
  { path: '/*', element: <PageNotFound /> },
  { path: '/', element: <LoginPage /> },
  { path: '/register', element: <RegisterPage /> },
  { path: '/verify-email-request', element: <VerifyEmailRequest /> },
  { path: '/mockmail', element: <MockMail /> },
  { path: '/verify-email', element: <VerifiedEmail /> },
  { path: '/password/reset', element: <ResetPasswordPage /> },
  { path: '/password/forget', element: <ForgetPasswordPage /> },
  { path: '/dashboard', element: <DashboardPage /> }
]);

const App = () => {
  return (
    <main>
      <RouterProvider router={router} />
      <Toaster />
    </main>
  )
}

export default App