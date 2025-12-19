<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Head, router } from '@inertiajs/vue3';
import { ShieldIcon, UserIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
  roles: Array<{ id: number; name: string }>;
  permissions: Array<{ id: number; name: string }>;
}

interface Role {
  id: number;
  name: string;
  permissions: Array<{ id: number; name: string }>;
}

interface Permission {
  id: number;
  name: string;
}

interface Props {
  users: User[];
  roles: Role[];
  permissions: Permission[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedUser = ref<User | null>(null);
const selectedRole = ref<string>('');

const filteredUsers = computed(() => {
  if (!searchQuery.value) {
    return props.users;
  }
  const query = searchQuery.value.toLowerCase();
  return props.users.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
  );
});

const assignRole = (userId: number, roleName: string) => {
  router.post(
    `/admin/users/${userId}/assign-role`,
    { role: roleName },
    {
      preserveScroll: true,
      onSuccess: () => {
        alert('Role assigned successfully!');
      },
    }
  );
};

const removeRole = (userId: number, roleName: string) => {
  if (confirm(`Are you sure you want to remove the ${roleName} role from this user?`)) {
    router.post(
      `/admin/users/${userId}/remove-role`,
      { role: roleName },
      {
        preserveScroll: true,
        onSuccess: () => {
          alert('Role removed successfully!');
        },
      }
    );
  }
};

const getRoleBadgeClass = (roleName: string) => {
  const classes: Record<string, string> = {
    Admin: 'bg-red-100 text-red-800',
    Publisher: 'bg-blue-100 text-blue-800',
    Editor: 'bg-green-100 text-green-800',
  };
  return classes[roleName] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <Head title="User Permissions & Roles" />

  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">User Permissions & Roles</h1>
        <p class="text-muted-foreground mt-2">
          Manage user roles and permissions across the system
        </p>
      </div>
    </div>

    <!-- Roles Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card v-for="role in roles" :key="role.id">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <ShieldIcon class="h-5 w-5" />
            {{ role.name }}
          </CardTitle>
          <CardDescription>
            {{ role.permissions.length }} permissions
          </CardDescription>
        </CardHeader>
        <CardContent>
          <ul class="space-y-1 text-sm">
            <li
              v-for="permission in role.permissions"
              :key="permission.id"
              class="text-muted-foreground"
            >
              • {{ permission.name }}
            </li>
          </ul>
        </CardContent>
      </Card>
    </div>

    <!-- Users Table -->
    <Card>
      <CardHeader>
        <CardTitle>User Role Management</CardTitle>
        <CardDescription>Assign roles to users</CardDescription>
        <div class="pt-4">
          <Input
            v-model="searchQuery"
            type="search"
            placeholder="Search users by name or email..."
            class="max-w-md"
          />
        </div>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>User</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>Current Roles</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="user in filteredUsers" :key="user.id">
              <TableCell>
                <div class="flex items-center gap-2">
                  <UserIcon class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium">{{ user.name }}</span>
                </div>
              </TableCell>
              <TableCell class="text-muted-foreground">
                {{ user.email }}
              </TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="px-2 py-1 text-xs rounded-full"
                    :class="getRoleBadgeClass(role.name)"
                  >
                    {{ role.name }}
                    <button
                      @click="removeRole(user.id, role.name)"
                      class="ml-1 hover:text-red-600"
                    >
                      ×
                    </button>
                  </span>
                  <span
                    v-if="user.roles.length === 0"
                    class="text-sm text-muted-foreground"
                  >
                    No roles assigned
                  </span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Select @update:model-value="(value) => assignRole(user.id, value as string)">
                    <SelectTrigger class="w-[150px]">
                      <SelectValue placeholder="Assign role" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="role in roles"
                        :key="role.id"
                        :value="role.name"
                        :disabled="user.roles.some((r) => r.name === role.name)"
                      >
                        {{ role.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>
