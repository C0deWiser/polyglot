<template>
  <div>
    <div class="progress" style="height: 5px">
      <div class="progress-bar bg-success" :style="'width: ' + (stat.progress * 100) + '%'"></div>
      <div class="progress-bar bg-warning" :style="'width: ' + (stat.problems * 100) + '%'"></div>
    </div>

  </div>
</template>

<script>
export default {
  name: "ProgressBar",
  props: ['stat'],
}
</script>

<style scoped>

</style>