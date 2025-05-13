<template>
  <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
    <h2 class="text-3xl font-bold text-[#b02e2e] mb-4">Membres archivés</h2>

    <!-- Archived Characters Table -->
    <div class="overflow-y-auto max-h-96">
      <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
        <thead class="sticky top-0 bg-[#b02e2e] z-10">
          <tr class="text-[#f3d9b1] text-lg">
            <th class="p-4">Classe</th>
            <th class="p-4">Nom</th>
            <th class="p-4">Recruteur</th>
            <th class="p-4">Rang</th>
            <th class="p-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="({ member }, index) in members"
              :key="`archived-${member.id}-${index}`"
              class="transition-all group relative hover:bg-[#f3d9b1]/30">
            <td class="p-4">
              <img
                :src="classes[member.class]"
                alt="Character Class"
                class="w-12 h-12 rounded-full mx-auto"
              />
            </td>
            <td class="p-4 text-[#b07d46] font-bold">
              {{ member.pseudo || 'Unknown' }}
            </td>
            <td class="p-4 text-[#b07d46]">
              {{ member?.recruiter?.pseudo || 'No Recruiter' }}
            </td>
            <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
            <td class="p-4">
              <button
                @click="$emit('unarchive', member)"
                class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
              >
                Restaurer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    members: {
      type: Array,
      required: true
    },
    classes: {
      type: Object,
      required: true
    }
  }
};
</script>
