<template>
  <div class="progress" style="height: 4px;">
    <div class="progress-bar bg-success" :style="'width: ' + (stat.progress * 100) + '%'"
         data-toggle="tooltip" data-placement="top" :title="stat.translated + ' translated string(s)'"></div>
    <div class="progress-bar bg-warning" :style="'width: ' + (stat.problems * 100) + '%'"
         data-toggle="tooltip" data-placement="top" :title="stat.fuzzy + ' fuzzy string(s)'"></div>
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