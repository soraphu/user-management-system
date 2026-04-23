import React from 'react'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import './App.css'

//Page imports
import HomePage from './pages/Home';
import LoginPage from './pages/Login';
import RegisterPage from './pages/Register';
import MockMail from './pages/MockMail';
import ResetPasswordPage from './pages/ResetPassword';
import ForgetPasswordPage from './pages/ForgetPassword';
import DashboardPage from './pages/Dashboard';

const router = createBrowserRouter([
  { path: '/', element: <HomePage /> },
  { path: '/login', element: <LoginPage /> },
  { path: '/register', element: <RegisterPage /> },
  { path: '/mockmail', element: <MockMail /> },
  { path: '/password/reset', element: <ResetPasswordPage /> },
  { path: '/password/forget', element: <ForgetPasswordPage /> },
  { path: '/dashboard', element: <DashboardPage /> }
]);

const App = () => {
  return (
    <div>
      <RouterProvider router={router} />
    </div>
  )
}

export default App