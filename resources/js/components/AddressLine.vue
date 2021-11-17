<template>
  <ol class="breadcrumb bg-transparent-">

    <li v-if="path.root" class="breadcrumb-item active">lang</li>
    <li v-else class="breadcrumb-item">
      <router-link :to="{ name: 'L10n' }">lang</router-link>
    </li>

    <li v-for="parent in parents" v-bind:key="parent.path" class="breadcrumb-item">
      <router-link
          :to="{ name: 'L10n', hash : '#' + parent.path }">{{ parent.name }}</router-link>
    </li>

    <li v-if="current" class="breadcrumb-item active">{{ current.name }}</li>
  </ol>
</template>

<script>
export default {
  name: "AddressLine",
  props: ["path"],

  data() {
    return {
      parents: [],
      current: ''
    };
  },

  watch: {
    path() {
      this.preparePath();
    }
  },

  mounted() {
    this.preparePath();
  },

  methods: {
    preparePath() {
      this.parents = [];
      this.current = '';
      let parents = [];

      if (this.path.relative) {
        this.path.relative.split('/').forEach(parent => {
          parents.push(
              (parents.length ? (parents.slice(-1) + '/') : '') + parent
          )
        });
      }

      parents.forEach(path => {
        this.parents.push({name: path.split('/').pop(), path: path})
      });

      this.current = this.parents.length ? this.parents.pop() : '';
    }
  },
}
</script>

<style scoped>

</style>