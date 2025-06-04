import { ref, provide, inject, Ref } from 'vue'

interface ModalState {
  isModalOpen: Ref<boolean>
  openModal: () => void
  closeModal: () => void
  toggleModal: () => void
}

const ModalSymbol = Symbol('modal')

export function provideModal(): ModalState {
  const isModalOpen = ref(false)

  const openModal = () => {
    isModalOpen.value = true
  }

  const closeModal = () => {
    isModalOpen.value = false
  }

  const toggleModal = () => {
    isModalOpen.value = !isModalOpen.value
  }

  const modalState: ModalState = {
    isModalOpen,
    openModal,
    closeModal,
    toggleModal
  }

  provide(ModalSymbol, modalState)

  return modalState
}

export function useModal(): ModalState {
  const modal = inject<ModalState>(ModalSymbol)
  if (!modal) {
    throw new Error('Modal not provided')
  }
  return modal
}
