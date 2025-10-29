import { createRouter, createWebHashHistory } from 'vue-router'
import Subjects from '../pages/subjects/subjects.vue'
import Home from '../pages/index/Home.vue'
import Profile from '../pages/profile/profile.vue'
import Category from '../pages/Category/Category.vue'
import PDF from '../pages/pdf/pdf.vue'
import Search from '../pages/search/search.vue'
import Request from '../pages/request/request.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/subjects',
    name: 'Subjects',
    component: Subjects
  },
  {
    path: '/category',
    name: 'Category',
    component: Category
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile
  },
  {
    path: '/pdf',
    name: 'PDF',
    component: PDF
  },
  {
    path: '/request',
    name: 'Request',
    component: Request
  },
  {
    path: '/search',
    name: 'Search',
    component: Search
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router