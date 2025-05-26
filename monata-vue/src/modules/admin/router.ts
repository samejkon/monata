import DashBoard from './views/DashBoard.vue'
import RoomList from './views/room/Room-list.vue'
import RoomCreate from './views/room/Room-create.vue'

export default [
  {
    path: '/dashboard',
    component: DashBoard,
  },
  {
    path: '/room-list',
    component: RoomList,
  },
  {
    path: '/room-form',
    component: RoomCreate,
  },
]
