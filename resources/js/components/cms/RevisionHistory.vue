<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { router } from '@inertiajs/vue3';
import { ClockIcon, RotateCcwIcon, UserIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Revision {
  id: number;
  post_id: number;
  user: {
    id: number;
    name: string;
    email: string;
  };
  title: string | Record<string, string>;
  slug: string;
  content: any;
  excerpt: string | Record<string, string>;
  status: string;
  is_featured: boolean;
  seo_data: any;
  reason: string | null;
  created_at: string;
}

interface Props {
  revisions: Revision[];
  postId: number;
}

const props = defineProps<Props>();

const selectedRevision = ref<Revision | null>(null);
const showCompareDialog = ref(false);

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getTitle = (title: string | Record<string, string>) => {
  if (typeof title === 'string') {
    return title;
  }
  return title.en || title.vi || title.jp || 'Untitled';
};

const restoreRevision = (revisionId: number) => {
  if (
    confirm(
      'Are you sure you want to restore this revision? Your current changes will be saved as a new revision.'
    )
  ) {
    router.post(
      `/admin/posts/${props.postId}/revisions/${revisionId}/restore`,
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          alert('Revision restored successfully!');
        },
      }
    );
  }
};

const viewRevisionDetails = (revision: Revision) => {
  selectedRevision.value = revision;
  showCompareDialog.value = true;
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Revision History</CardTitle>
      <CardDescription>
        View and restore previous versions of this post
      </CardDescription>
    </CardHeader>
    <CardContent>
      <ScrollArea class="h-[400px] pr-4">
        <div v-if="revisions.length === 0" class="text-center py-8 text-muted-foreground">
          No revisions yet
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="revision in revisions"
            :key="revision.id"
            class="flex items-start gap-4 rounded-lg border p-4 hover:bg-accent/50 transition-colors"
          >
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <ClockIcon class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm font-medium">
                  {{ formatDate(revision.created_at) }}
                </span>
                <span
                  class="px-2 py-1 text-xs rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': revision.status === 'published',
                    'bg-yellow-100 text-yellow-800': revision.status === 'pending_review',
                    'bg-gray-100 text-gray-800': revision.status === 'draft',
                    'bg-red-100 text-red-800': revision.status === 'archived',
                  }"
                >
                  {{ revision.status }}
                </span>
              </div>
              <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <UserIcon class="h-4 w-4" />
                <span>{{ revision.user.name }}</span>
              </div>
              <div v-if="revision.reason" class="text-sm italic text-muted-foreground">
                {{ revision.reason }}
              </div>
              <div class="text-sm font-medium">
                {{ getTitle(revision.title) }}
              </div>
            </div>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" @click="viewRevisionDetails(revision)">
                View
              </Button>
              <Button
                variant="outline"
                size="sm"
                @click="restoreRevision(revision.id)"
              >
                <RotateCcwIcon class="h-4 w-4 mr-1" />
                Restore
              </Button>
            </div>
          </div>
        </div>
      </ScrollArea>
    </CardContent>
  </Card>

  <!-- Revision Details Dialog -->
  <Dialog v-model:open="showCompareDialog">
    <DialogContent class="max-w-3xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Revision Details</DialogTitle>
        <DialogDescription v-if="selectedRevision">
          Created on {{ formatDate(selectedRevision.created_at) }} by
          {{ selectedRevision.user.name }}
        </DialogDescription>
      </DialogHeader>
      <div v-if="selectedRevision" class="space-y-4">
        <div>
          <h4 class="font-medium mb-2">Title</h4>
          <p class="text-sm">{{ getTitle(selectedRevision.title) }}</p>
        </div>
        <div>
          <h4 class="font-medium mb-2">Slug</h4>
          <p class="text-sm font-mono">{{ selectedRevision.slug }}</p>
        </div>
        <div>
          <h4 class="font-medium mb-2">Status</h4>
          <span
            class="px-2 py-1 text-xs rounded-full"
            :class="{
              'bg-green-100 text-green-800': selectedRevision.status === 'published',
              'bg-yellow-100 text-yellow-800':
                selectedRevision.status === 'pending_review',
              'bg-gray-100 text-gray-800': selectedRevision.status === 'draft',
              'bg-red-100 text-red-800': selectedRevision.status === 'archived',
            }"
          >
            {{ selectedRevision.status }}
          </span>
        </div>
        <div v-if="selectedRevision.reason">
          <h4 class="font-medium mb-2">Reason</h4>
          <p class="text-sm italic">{{ selectedRevision.reason }}</p>
        </div>
        <div class="flex gap-4 pt-4 border-t">
          <Button variant="outline" @click="showCompareDialog = false">
            Close
          </Button>
          <Button @click="restoreRevision(selectedRevision.id)">
            <RotateCcwIcon class="h-4 w-4 mr-2" />
            Restore This Revision
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
