import { defineStore } from 'pinia'

export const usePdvSelectionStore = defineStore('pdvSelection', {
  state: () => ({
    selectedPdvId: null
  }),
  actions: {
    setSelectedPdvId(id) {
      this.selectedPdvId = id
    },
    clearSelectedPdvId() {
      this.selectedPdvId = null
    }
  }
})
