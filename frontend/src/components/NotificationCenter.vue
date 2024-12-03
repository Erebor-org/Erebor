<template>
  <div
    v-if="visible"
    class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg shadow-lg z-50"
    @click="hideNotification"
  >
    {{ message }}
  </div>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
      message: '',
    };
  },
  methods: {
    showNotification(msg) {
      this.message = msg;
      this.visible = true;
      setTimeout(() => {
        this.visible = false;
      }, 3000); // Notification disappears after 3 seconds
    },
    hideNotification() {
      this.visible = false;
    },
  },
};
</script>

<style scoped>
/* Add additional styles for the notification if needed */
</style>
