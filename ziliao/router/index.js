import { createRouter, createWebHistory } from 'vue-router'
import Index from '../pages/index/index.vue'
import Subjects from '../pages/subjects/subjects.vue'
import PDF from '../pages/pdf/pdf.vue'

const routes = [
  {
    path: '/',
    name: 'Index',
    component: Index
  },
  {
    path: '/subjects',
    name: 'Subjects',
    component: Subjects
  },
  {
    path: '/pdf',
    name: 'PDF',
    component: PDF
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router