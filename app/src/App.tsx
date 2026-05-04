import { AuthProvider } from './auth/AuthContext';
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { Toaster } from 'sonner';
import './App.css'

//Page imports
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import MockMail from './pages/MockMail';
import SendVerifyEmail from './pages/SendVerifyEmail';
import ResetPasswordPage from './pages/ResetPassword';
import ForgetPasswordPage from './pages/ForgetPassword';
import HomePage from './pages/Home';
import PageNotFound from './pages/PageNotFound';
import VerifiedEmail from './pages/EmailVerfied';
import UserProfileSettingPage from './pages/ProfileSetting';
import AdminDashBoardPage from './pages/AdminDashBoard';

const router = createBrowserRouter([
  { path: '/*', element: <PageNotFound /> },

  { path: '/', element: <LoginPage /> },

  { path: '/register', element: <RegisterPage /> },

  { path: '/verify-email-request', element: <SendVerifyEmail /> },
  { path: '/verify-email', element: <VerifiedEmail /> },

  { path: '/mockmail', element: <MockMail /> },

  { path: '/password/reset', element: <ResetPasswordPage /> },
  { path: '/password/forget', element: <ForgetPasswordPage /> },

  { path: '/home', element: <HomePage /> },

  { path: '/admin/dashboard', element: <AdminDashBoardPage /> },

  { path: '/user/setting/profile', element: <UserProfileSettingPage /> }
]);

const App = () => {
  return (
    <main>
      <AuthProvider>
        <RouterProvider router={router} />
        <Toaster />
      </AuthProvider>
    </main>
  )
}

export default App