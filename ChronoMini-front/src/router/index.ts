// router.ts
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import HelpView from '@/views/HelpView.vue'
import AccountView from '@/views/AccountInfo.vue'
import DeleteAccount from '@/views/DeleteAccount.vue'
import RegisterLogin from '@/views/RegisterLogin.vue'
import Kids from '@/views/KidsList.vue'
import KidsAdd from '@/views/KidAdd.vue'
import KidDetail from '@/views/KidDetail.vue'
import Recording  from '@/views/Recording.vue'
import KidRecordList from '@/views/KidRecordList.vue'
import RecordDetail from '@/views/RecordDetail.vue'
import ConnectUsers from '@/views/ConnectUsers.vue'
import VerificationView from '@/views/VerificationView.vue'
import ForgotPassword from '@/views/ForgotPassword.vue';
import ResetPassword from '@/views/ResetPassword.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import UnauthorizedPage from '@/views/UnauthorizedPage.vue'
import Legals from '@/views/Legals.vue'
import Confid from '@/views/Confid.vue'
import { useAuthStore } from '@/stores/auth';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/help',
      name: 'help',
      component: HelpView
    },
    {
      path: '/account',
      name: 'account',
      component : AccountView,
      meta: { requiresAuth: true }
    },
    {
      path: '/account/delete',
      name: 'account/delete',
      component: DeleteAccount,
      meta: { requiresAuth: true }
    },
    {
      path: '/register-login',
      name: 'register-login',
      component: RegisterLogin,

    },
    {
      path: '/kids',
      name: 'kids',
      component: Kids,
      meta: { requiresAuth: true }
    },
    {
      path: '/kids/add',
      name: 'kids/add',
      component: KidsAdd,
      meta: { requiresAuth: true }
    },
    {
      path: '/kids/:id', // Route dynamique pour la page de détail
      name: 'KidDetails',
      component: KidDetail,
      meta: { requiresAuth: true }
    },
    {
      path: '/record/:id',
      name: 'Recording',
      component: Recording,
      meta: { requiresAuth: true }
    },
    {
      path: '/kid/:id/records',
      name: 'KidRecordList',
      component: KidRecordList
    },
    {
      path: '/recordDetail/:id',
      name: 'RecordDetail',
      component: RecordDetail,
      meta: { requiresAuth: true }
    },
    {
      path: '/link-user',
      name: 'userConnections',
      component: ConnectUsers,
      meta: { requiresAuth: true }
    },
    {
      path: '/verification',
      name: 'verification',
      component: VerificationView
    },
    {
      path: '/forgot-password',
      name: 'ForgotPassword',
      component: ForgotPassword
    },
    {
      path: '/reset-password',
      name: 'ResetPassword',
      component: ResetPassword
    },
    {
      path: '/legals',
      name: 'MentionsLegales',
      component: Legals
    },
    {
      path: '/confid',
      name: 'PolitiqueConfidentialite',
      component: Confid
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminDashboard,
      meta: { requiresAdmin: true },
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore();
        if (!authStore.isAdmin) {
          next({ name: 'unauthorized' });
        } else {
          next();
        }
      }
    },
    {
      path: '/unauthorized',
      name: 'unauthorized',
      component: UnauthorizedPage
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      };
    }
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0 };
  }
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  // Si la route requiert une authentification et que l'utilisateur n'est pas connecté
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('Navigation bloquée : authentification requise');
    next({ name: 'register-login' });
  } 
  // Si la route requiert des droits admin et que l'utilisateur n'est pas admin
  else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    console.log('Navigation bloquée : droits administrateur requis');
    next({ name: 'unauthorized' });
  } 
  // Sinon, permettre la navigation
  else {
    next();
  }
});

export default router
