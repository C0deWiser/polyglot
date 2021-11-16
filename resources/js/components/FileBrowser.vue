<script>
import ProgressBar from "./ProgressBar";

export default {
  name: "FileBrowser",
  props: ["files"],

  /**
   * Components
   */
  components: {
    ProgressBar: ProgressBar
  },
}
</script>

<template>
  <table class="table table-hover table-sm mb-0">
    <tbody>
    <tr v-for="file in files">
      <td style="text-indent: 1em">
        <router-link :to="{ name: 'L10n', hash : '#' + file.relative }">
          {{ file.basename }}
        </router-link>
      </td>
      <td>
        <ProgressBar v-if="file.stat" :stat="file.stat"></ProgressBar>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<style scoped>

</style>