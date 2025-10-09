<template>
    <q-drawer
        v-model="modelValue"
        side="right"
        overlay
        behavior="mobile"
        class="bg-grey-1"
        :width="400"
    >
        <q-toolbar class="bg-primary text-white">
            <q-toolbar-title>{{ event?.title || 'Assignments' }}</q-toolbar-title>
            <q-btn icon="close" flat round dense @click="closeDrawer" />
        </q-toolbar>

        <q-scroll-area class="fit">
            <div v-if="loading" class="q-pa-md flex flex-center">
                <q-spinner-dots color="primary" size="40px" />
            </div>

            <div v-else class="q-pa-md">
                <div v-for="duty in eventDuties" :key="duty.id" class="q-mb-md">
                    <q-card flat bordered class="q-pa-sm">
                        <div class="text-subtitle1">{{ duty.duty.name }}</div>
                        <div class="text-caption text-grey-8">Required: {{ duty.min_required ?? duty.duty.min_required }}</div>

                        <q-list dense bordered class="q-mt-sm rounded-borders">
                            <q-item v-for="a in duty.assignments" :key="a.id">
                                <q-item-section>{{ a.employee.name }}</q-item-section>
                                <q-item-section side>
                                    <q-btn icon="delete" size="sm" color="negative" flat round @click="removeAssignment(a.id)" />
                                </q-item-section>
                            </q-item>
                        </q-list>

                        <div class="q-mt-sm flex justify-end">
                            <q-btn icon="person_add" label="Assign" color="primary" flat dense @click="openAssignDialog(duty)" />
                        </div>
                    </q-card>
                </div>
            </div>
        </q-scroll-area>

        <q-dialog v-model="showDialog">
            <q-card style="min-width: 350px">
                <q-card-section class="text-h6">Assign Employee</q-card-section>
                <q-separator />
                <q-card-section>
                    <q-select
                        v-model="selectedEmployee"
                        :options="employees"
                        option-label="name"
                        option-value="id"
                        label="Select Employee"
                        dense
                    />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancel" v-close-popup />
                    <q-btn label="Assign" color="primary" @click="assignEmployee" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </q-drawer>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'

const props = defineProps({
    modelValue: Boolean,
    event: Object
})
const emit = defineEmits(['update:modelValue', 'updated'])

const $q = useQuasar()
const eventDuties = ref([])
const loading = ref(false)
const showDialog = ref(false)
const selectedDuty = ref(null)
const selectedEmployee = ref(null)
const employees = ref([])

watch(() => props.modelValue, async (val) => {
    if (val && props.event) {
        await loadEventDuties()
    }
})

const closeDrawer = () => emit('update:modelValue', false)

const loadEventDuties = async () => {
    loading.value = true
    try {
        const res = await axios.get(`/api/events/${props.event.id}/duties`)
        eventDuties.value = res.data
    } catch (err) {
        console.error(err)
    } finally {
        loading.value = false
    }
}

const openAssignDialog = async (duty) => {
    selectedDuty.value = duty
    try {
        const res = await axios.get(`/api/employees?duty=${duty.duty.name}`)
        employees.value = res.data
        showDialog.value = true
    } catch (err) {
        $q.notify({ type: 'negative', message: 'Failed to load employees' })
    }
}

const assignEmployee = async () => {
    if (!selectedEmployee.value) return
    try {
        await axios.post('/api/assignments', {
            event_duty_id: selectedDuty.value.id,
            employee_id: selectedEmployee.value
        })
        $q.notify({ type: 'positive', message: 'Employee assigned successfully' })
        showDialog.value = false
        await loadEventDuties()
        emit('updated')
    } catch (err) {
        $q.notify({ type: 'negative', message: 'Failed to assign employee' })
    }
}

const removeAssignment = async (assignmentId) => {
    try {
        await axios.delete(`/api/assignments/${assignmentId}`)
        $q.notify({ type: 'warning', message: 'Assignment removed' })
        await loadEventDuties()
        emit('updated')
    } catch (err) {
        $q.notify({ type: 'negative', message: 'Failed to remove assignment' })
    }
}
</script>
