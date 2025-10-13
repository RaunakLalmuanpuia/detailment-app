<template>
    <q-page class="container q-py-lg">

        <!-- Header -->
        <div class="row items-center justify-between q-mb-md">
            <div class="text-h5 text-weight-bold text-primary">Events Overview</div>
            <q-btn color="primary" label="Create Event" @click="openCreateDialog()" />
        </div>

        <!-- Event List -->
        <q-list bordered separator class="rounded-borders">
            <q-item
                v-for="event in props.events"
                :key="event.id"
                clickable
                @click="openViewDialog(event)"
            >
                <q-item-section>
                    <div class="text-weight-bold">{{ event.title }}</div>
                    <div class="text-caption text-grey">
                        {{ formatDate(event.date) }} | {{ event.location }}
                    </div>
                </q-item-section>

                <q-item-section side>
                    <q-chip
                        :color="getStatusColor(event.status)"
                        text-color="white"
                        size="sm"
                        class="text-capitalize"
                    >
                        {{ event.status }}
                    </q-chip>
                </q-item-section>

                <q-item-section side>
                    <q-btn dense flat icon="edit" color="primary" @click.stop="openEditDialog(event)" />
                    <q-btn dense flat icon="delete" color="negative" @click.stop="confirmDelete(event)" />
                </q-item-section>
            </q-item>
        </q-list>

        <!-- CREATE EVENT DIALOG -->
        <q-dialog v-model="createDialog">
            <q-card style="min-width: 800px">
                <q-card-section class="text-h6 text-weight-bold text-primary">
                    Create New Event
                </q-card-section>
                <q-separator />
                <q-card-section>
                    <q-form @submit.prevent="submitCreate">

                        <!-- BASIC INFO -->
                        <div class="row q-col-gutter-md">
                            <div class="col-6">
                                <q-input v-model="createForm.title" label="Event Title" outlined dense />
                            </div>
                            <div class="col-6">
                                <q-input v-model="createForm.location" label="Location" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="createForm.date" type="date" label="Date" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="createForm.start_time" type="time" label="Start Time" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="createForm.end_time" type="time" label="End Time" outlined dense />
                            </div>
                            <div class="col-12">
                                <q-select
                                    v-model="createForm.status"
                                    label="Status"
                                    :options="['upcoming', 'completed', 'cancelled']"
                                    outlined
                                    dense
                                />
                            </div>
                        </div>

                        <!-- GUESTS -->
                        <div class="q-mt-md">
                            <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Guests</div>
                            <div v-for="(guest, i) in createForm.guests" :key="i" class="q-mb-sm row q-col-gutter-md">
                                <div class="col-3"><q-input v-model="guest.role" label="Role" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.name" label="Name" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.designation" label="Designation" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.organization" label="Organization" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.contact" label="Contact" outlined dense /></div>
                                <div class="col-auto flex items-center">
                                    <q-btn dense flat icon="delete" color="negative" @click="removeCreateGuest(i)" />
                                </div>
                            </div>
                            <q-btn dense flat icon="add" color="primary" label="Add Guest" @click="addCreateGuest" />
                        </div>

                        <!-- DUTIES -->
                        <div class="q-mt-lg">
                            <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Duty Assignments</div>
                            <div
                                v-for="(dutyAssign, dIndex) in createForm.assignments"
                                :key="dutyAssign.duty.id"
                                class="q-pa-md q-mb-md bg-grey-2 rounded-borders"
                            >
                                <div class="text-weight-bold text-primary text-lg q-mb-sm">{{ dutyAssign.duty.name }}</div>

                                <div
                                    v-for="(emp, eIndex) in dutyAssign.employees"
                                    :key="eIndex"
                                    class="row q-col-gutter-md q-mb-sm"
                                >
                                    <div class="col-10">
                                        <q-select
                                            v-model="emp.employee_id"
                                            label="Select Employee"
                                            :options="dutyAssign.availableEmployees"
                                            option-value="id"
                                            option-label="name"
                                            outlined
                                            dense
                                            emit-value
                                            map-options
                                        />
                                    </div>
                                    <div class="col-auto flex items-center">
                                        <q-btn dense flat icon="delete" color="negative" @click="removeCreateEmployee(dIndex, eIndex)" />
                                    </div>
                                </div>

                                <q-btn dense flat icon="add" color="primary" label="Add Employee" @click="addCreateEmployee(dIndex)" />
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <q-card-actions align="right" class="q-mt-md">
                            <q-btn flat label="Cancel" color="secondary" v-close-popup />
                            <q-btn label="Create Event" color="primary" type="submit" />
                        </q-card-actions>

                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- EDIT EVENT DIALOG -->
        <q-dialog v-model="editDialog">
            <q-card style="min-width: 800px">
                <q-card-section class="text-h6 text-weight-bold text-primary">
                    Edit Event
                </q-card-section>
                <q-separator />
                <q-card-section>
                    <q-form @submit.prevent="submitEdit">

                        <!-- BASIC INFO -->
                        <div class="row q-col-gutter-md">
                            <div class="col-6">
                                <q-input v-model="editForm.title" label="Event Title" outlined dense />
                            </div>
                            <div class="col-6">
                                <q-input v-model="editForm.location" label="Location" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="editForm.date" type="date" label="Date" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="editForm.start_time" type="time" label="Start Time" outlined dense />
                            </div>
                            <div class="col-4">
                                <q-input v-model="editForm.end_time" type="time" label="End Time" outlined dense />
                            </div>
                            <div class="col-12">
                                <q-select
                                    v-model="editForm.status"
                                    label="Status"
                                    :options="['upcoming', 'completed', 'cancelled']"
                                    outlined
                                    dense
                                />
                            </div>
                        </div>

                        <!-- GUESTS -->
                        <div class="q-mt-md">
                            <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Guests</div>
                            <div v-for="(guest, i) in editForm.guests" :key="i" class="q-mb-sm row q-col-gutter-md">
                                <div class="col-3"><q-input v-model="guest.role" label="Role" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.name" label="Name" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.designation" label="Designation" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.organization" label="Organization" outlined dense /></div>
                                <div class="col-3"><q-input v-model="guest.contact" label="Contact" outlined dense /></div>
                                <div class="col-auto flex items-center">
                                    <q-btn dense flat icon="delete" color="negative" @click="removeEditGuest(i)" />
                                </div>
                            </div>
                            <q-btn dense flat icon="add" color="primary" label="Add Guest" @click="addEditGuest" />
                        </div>

                        <!-- DUTIES -->
                        <div class="q-mt-lg">
                            <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Duty Assignments</div>
                            <div
                                v-for="(dutyAssign, dIndex) in editForm.assignments"
                                :key="dutyAssign.duty.id"
                                class="q-pa-md q-mb-md bg-grey-2 rounded-borders"
                            >
                                <div class="text-weight-bold text-primary text-lg q-mb-sm">{{ dutyAssign.duty.name }}</div>

                                <div
                                    v-for="(emp, eIndex) in dutyAssign.employees"
                                    :key="eIndex"
                                    class="row q-col-gutter-md q-mb-sm"
                                >
                                    <div class="col-10">
                                        <q-select
                                            v-model="emp.employee_id"
                                            label="Select Employee"
                                            :options="dutyAssign.availableEmployees"
                                            option-value="id"
                                            option-label="name"
                                            outlined
                                            dense
                                            emit-value
                                            map-options
                                        />
                                    </div>
                                    <div class="col-auto flex items-center">
                                        <q-btn dense flat icon="delete" color="negative" @click="removeEditEmployee(dIndex, eIndex)" />
                                    </div>
                                </div>

                                <q-btn dense flat icon="add" color="primary" label="Add Employee" @click="addEditEmployee(dIndex)" />
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <q-card-actions align="right" class="q-mt-md">
                            <q-btn flat label="Cancel" color="secondary" v-close-popup />
                            <q-btn label="Save Changes" color="primary" type="submit" />
                        </q-card-actions>

                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- VIEW EVENT DETAILS DIALOG -->
        <q-dialog v-model="viewDialog">
            <q-card style="min-width: 700px; max-width: 90vw;">
                <q-card-section class="text-h6 text-weight-bold text-primary">{{ selectedEvent?.title }}</q-card-section>
                <q-separator />
                <q-card-section>
                    <div class="q-mb-md">
                        <div><b>Date:</b> {{ formatDate(selectedEvent?.date) }}</div>
                        <div><b>Time:</b> {{ selectedEvent?.start_time }} - {{ selectedEvent?.end_time }}</div>
                        <div><b>Location:</b> {{ selectedEvent?.location }}</div>
                        <div><b>Status:</b>
                            <q-chip :color="getStatusColor(selectedEvent?.status)" text-color="white" class="text-capitalize">
                                {{ selectedEvent?.status }}
                            </q-chip>
                        </div>
                    </div>

                    <div v-if="selectedEvent?.guests?.length">
                        <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Guests</div>
                        <q-markup-table flat bordered>
                            <thead>
                            <tr>
                                <th>Role</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Organization</th>
                                <th>Contact</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(guest, i) in selectedEvent.guests" :key="i">
                                <td>{{ guest.role }}</td>
                                <td>{{ guest.name }}</td>
                                <td>{{ guest.designation }}</td>
                                <td>{{ guest.organization }}</td>
                                <td>{{ guest.contact }}</td>
                            </tr>
                            </tbody>
                        </q-markup-table>
                    </div>

                    <div v-if="selectedEvent?.duties?.length" class="q-mt-md">
                        <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Duty Assignments</div>
                        <div v-for="(duty, i) in selectedEvent.duties" :key="i" class="q-pa-sm bg-grey-2 rounded-borders q-mb-sm">
                            <div class="text-weight-bold text-primary">{{ duty.name }}</div>
                            <ul class="q-pl-md">
                                <li v-for="emp in duty.employees" :key="emp.id">{{ emp.name }}</li>
                            </ul>
                        </div>
                    </div>

                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Close" color="primary" v-close-popup />
                </q-card-actions>
            </q-card>
        </q-dialog>

    </q-page>
</template>


<script setup>
import BackendLayout from '../../../Layouts/BackendLayout.vue'
import { ref, watch } from 'vue'
import { router,useForm } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'

defineOptions({ layout: BackendLayout })

const props = defineProps(['events', 'duties', 'employees'])
const $q = useQuasar()

// Dialogs
const createDialog = ref(false)
const editDialog = ref(false)
const viewDialog = ref(false)
const selectedEvent = ref(null)

// Base data
const allEmployees = ref(props.employees)
const dutyOptions = ref(props.duties)

// ----------------- CREATE FORM -----------------
const createForm = useForm({
    title: '',
    date: '',
    start_time: '',
    end_time: '',
    location: '',
    status: 'upcoming',
    guests: [],
    assignments: []
})

// Initialize duties for create
const initializeCreateAssignments = () => {
    createForm.assignments = dutyOptions.value.map(duty => {
        const availableEmployees = allEmployees.value.filter(e =>
            (e.designation?.duty_type || '').toLowerCase().includes(duty.name.toLowerCase())
        )
        return { duty, availableEmployees, employees: [{ employee_id: null }] }
    })
}

// Guests & employees
const addCreateGuest = () => createForm.guests.push({ role: '', name: '', designation: '', organization: '', contact: '' })
const removeCreateGuest = (i) => createForm.guests.splice(i, 1)
const addCreateEmployee = (dIndex) => createForm.assignments[dIndex].employees.push({ employee_id: null })
const removeCreateEmployee = (dIndex, eIndex) => createForm.assignments[dIndex].employees.splice(eIndex, 1)

// Open create dialog
const openCreateDialog = () => {
    createForm.reset()
    initializeCreateAssignments()
    createDialog.value = true
}

// Submit create
const submitCreate = () => {
    createForm.post(route('events.store'), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event created successfully!' })
            createDialog.value = false
        }
    })
}

// ----------------- EDIT FORM -----------------
const editForm = useForm({
    id: null,
    title: '',
    date: '',
    start_time: '',
    end_time: '',
    location: '',
    status: 'upcoming',
    guests: [],
    assignments: []
})

// Open edit dialog
const openEditDialog = (event) => {
    editForm.id = event.id
    editForm.title = event.title
    editForm.location = event.location
    editForm.date = event.date
    editForm.start_time = event.start_time
    editForm.end_time = event.end_time
    editForm.status = event.status
    editForm.guests = event.guests?.map(g => ({ ...g })) || []

    // Map duties & employees
    editForm.assignments = dutyOptions.value.map(duty => {
        const eventDuty = event.duties?.find(d => d.id === duty.id)
        const availableEmployees = allEmployees.value.filter(e =>
            (e.designation?.duty_type || '').toLowerCase().includes(duty.name.toLowerCase())
        )
        return {
            duty,
            availableEmployees,
            employees: eventDuty?.employees?.length
                ? eventDuty.employees.map(emp => ({ employee_id: emp.id }))
                : [{ employee_id: null }]
        }
    })

    editDialog.value = true
}

// Guests & employees for edit
const addEditGuest = () => editForm.guests.push({ role: '', name: '', designation: '', organization: '', contact: '' })
const removeEditGuest = (i) => editForm.guests.splice(i, 1)
const addEditEmployee = (dIndex) => editForm.assignments[dIndex].employees.push({ employee_id: null })
const removeEditEmployee = (dIndex, eIndex) => editForm.assignments[dIndex].employees.splice(eIndex, 1)

// Submit edit
const submitEdit = () => {
    editForm.put(route('events.update', editForm.id), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event updated successfully!' })
            editDialog.value = false
        }
    })
}

// View event
const openViewDialog = (event) => {
    selectedEvent.value = event
    viewDialog.value = true
}

const confirmDelete = (event) => {
    $q.dialog({
        title: 'Confirm Delete',
        message: `Are you sure you want to delete the event "${event.title}"?`,
        cancel: true,
        persistent: true,
    }).onOk(() =>
        router.delete(route('events.destroy', event), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event deleted successfully!' })
        },
        onError: (errors) => {
            $q.notify({ type: 'negative', message: 'Failed to delete event.' })
            console.error(errors)
        }
    }))
}



// Helpers
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : ''
const getStatusColor = (status) => ({ upcoming: 'primary', completed: 'positive', cancelled: 'negative' })[status] || 'grey'

// CREATE FORM WATCHER (no event_id needed)
watch(
    () => [createForm.date, createForm.start_time, createForm.end_time],
    async ([date, start, end]) => {
        if (!date || !start || !end) return
        try {
            const { data } = await axios.post(route('events.available-employees'), {
                date,
                start_time: start,
                end_time: end,
            })
            createForm.assignments.forEach(assignment => {
                assignment.availableEmployees = data.filter(emp =>
                    (emp.designation?.duty_type || '').toLowerCase() === assignment.duty.name.toLowerCase()
                )
            })
        } catch (error) {
            console.error('Failed to fetch available employees', error)
        }
    }
)

// EDIT FORM WATCHER (pass event_id)
watch(
    () => [editForm.date, editForm.start_time, editForm.end_time],
    async ([date, start, end]) => {
        if (!date || !start || !end) return
        try {
            const { data } = await axios.post(route('events.available-employees'), {
                date,
                start_time: start,
                end_time: end,
                event_id: editForm.id, // pass current event id
            })
            editForm.assignments.forEach(assignment => {
                assignment.availableEmployees = data.filter(emp =>
                    (emp.designation?.duty_type || '').toLowerCase() === assignment.duty.name.toLowerCase()
                )
            })
        } catch (error) {
            console.error('Failed to fetch available employees', error)
        }
    }
)

</script>
