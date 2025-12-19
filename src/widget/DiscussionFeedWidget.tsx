import React, { useEffect, useState } from 'react';
import { MessageCircle, Calendar, User, ChevronRight, Loader2, RefreshCw } from 'lucide-react';

interface Author {
	id: number;
	name: string;
	avatar: string;
}

interface Comment {
	id: number;
	content: string;
	author: Author;
	date: string;
	timeAgo: string;
}

interface Forum {
	id: number;
	name: string;
	slug: string;
}

interface DiscussionPost {
	id: number;
	title: string;
	content: string;
	fullContent: string;
	excerpt: string;
	date: string;
	dateFormatted: string;
	timeAgo: string;
	author: Author;
	commentCount: number;
	permalink: string;
	coverImage: string;
	forum: Forum | null;
	comments?: Comment[];
}

interface DiscussionFeedProps {
	forumId?: string;
	limit?: number;
	showComments?: boolean;
	layout?: 'list' | 'cards' | 'compact';
	showHeader?: boolean;
	title?: string;
	showForum?: boolean;
	showAuthor?: boolean;
	showDate?: boolean;
	showExcerpt?: boolean;
	linkToPost?: boolean;
	restUrl: string;
	nonce: string;
}

export function DiscussionFeedWidget({
	forumId,
	limit = 10,
	showComments = false,
	layout = 'list',
	showHeader = true,
	title = 'Discussion Feed',
	showForum = true,
	showAuthor = true,
	showDate = true,
	showExcerpt = true,
	linkToPost = true,
	restUrl,
	nonce,
}: DiscussionFeedProps) {
	const [posts, setPosts] = useState<DiscussionPost[]>([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);
	const [page, setPage] = useState(1);
	const [hasMore, setHasMore] = useState(true);
	const [totalPosts, setTotalPosts] = useState(0);

	const fetchPosts = async (pageNum: number, append = false) => {
		try {
			setLoading(true);
			setError(null);

			const params = new URLSearchParams({
				limit: limit.toString(),
				page: pageNum.toString(),
				include_comments: showComments.toString(),
			});

			if (forumId) {
				params.append('forum_id', forumId);
			}

			const response = await fetch(`${restUrl}?${params}`, {
				headers: {
					'X-WP-Nonce': nonce,
				},
			});

			if (!response.ok) {
				throw new Error('Failed to fetch discussions');
			}

			const data = await response.json();

			if (data.success) {
				if (append) {
					setPosts((prev) => [...prev, ...data.data]);
				} else {
					setPosts(data.data);
				}
				setTotalPosts(data.meta.total);
				setHasMore(data.data.length === limit);
			} else {
				throw new Error('Failed to load discussions');
			}
		} catch (err) {
			setError(err instanceof Error ? err.message : 'An error occurred');
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		fetchPosts(1);
	}, [forumId, limit, showComments]);

	const loadMore = () => {
		const nextPage = page + 1;
		setPage(nextPage);
		fetchPosts(nextPage, true);
	};

	const refresh = () => {
		setPage(1);
		fetchPosts(1);
	};

	const PostCard = ({ post }: { post: DiscussionPost }) => {
		const Wrapper = linkToPost ? 'a' : 'div';
		const wrapperProps = linkToPost
			? { href: post.permalink, className: 'block' }
			: { className: 'block' };

		return (
			<Wrapper {...wrapperProps}>
				<div
					className={`
					bg-white rounded-lg border border-gray-200 overflow-hidden
					transition-all duration-200
					${linkToPost ? 'hover:shadow-md hover:border-blue-300 cursor-pointer' : ''}
					${layout === 'cards' ? 'h-full' : ''}
				`}
				>
					{/* Cover Image */}
					{post.coverImage && layout !== 'compact' && (
						<div className="aspect-video bg-gray-100 overflow-hidden">
							<img
								src={post.coverImage}
								alt={post.title}
								className="w-full h-full object-cover"
							/>
						</div>
					)}

					<div className={`p-4 ${layout === 'compact' ? 'py-3' : ''}`}>
						{/* Forum Badge */}
						{showForum && post.forum && (
							<span className="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full mb-2">
								{post.forum.name}
							</span>
						)}

						{/* Title */}
						<h3
							className={`font-semibold text-gray-900 ${layout === 'compact' ? 'text-sm' : 'text-lg'} ${linkToPost ? 'group-hover:text-blue-600' : ''}`}
						>
							{post.title}
						</h3>

						{/* Excerpt */}
						{showExcerpt && layout !== 'compact' && (
							<p className="text-gray-600 text-sm mt-2 line-clamp-2">
								{post.content}
							</p>
						)}

						{/* Meta Info */}
						<div
							className={`flex items-center gap-4 text-sm text-gray-500 ${layout === 'compact' ? 'mt-2' : 'mt-3'}`}
						>
							{/* Author */}
							{showAuthor && (
								<div className="flex items-center gap-2">
									{post.author.avatar ? (
										<img
											src={post.author.avatar}
											alt={post.author.name}
											className="w-5 h-5 rounded-full"
										/>
									) : (
										<User className="w-4 h-4" />
									)}
									<span className="truncate max-w-[100px]">
										{post.author.name}
									</span>
								</div>
							)}

							{/* Date */}
							{showDate && (
								<div className="flex items-center gap-1">
									<Calendar className="w-4 h-4" />
									<span>{post.timeAgo}</span>
								</div>
							)}

							{/* Comments */}
							<div className="flex items-center gap-1">
								<MessageCircle className="w-4 h-4" />
								<span>{post.commentCount}</span>
							</div>

							{/* Arrow for clickable */}
							{linkToPost && (
								<ChevronRight className="w-4 h-4 ml-auto text-gray-400" />
							)}
						</div>

						{/* Comment Previews */}
						{showComments && post.comments && post.comments.length > 0 && (
							<div className="mt-4 pt-3 border-t border-gray-100 space-y-2">
								<p className="text-xs font-medium text-gray-500 uppercase">
									Recent Comments
								</p>
								{post.comments.map((comment) => (
									<div
										key={comment.id}
										className="flex items-start gap-2 text-sm"
									>
										<img
											src={comment.author.avatar}
											alt={comment.author.name}
											className="w-6 h-6 rounded-full flex-shrink-0"
										/>
										<div className="min-w-0">
											<span className="font-medium text-gray-700">
												{comment.author.name}
											</span>
											<span className="text-gray-500 ml-1">
												{comment.content}
											</span>
										</div>
									</div>
								))}
							</div>
						)}
					</div>
				</div>
			</Wrapper>
		);
	};

	// Loading state
	if (loading && posts.length === 0) {
		return (
			<div className="frs-discussion-feed">
				{showHeader && (
					<div className="flex items-center justify-between mb-4">
						<h2 className="text-xl font-bold text-gray-900">{title}</h2>
					</div>
				)}
				<div className="flex items-center justify-center py-12">
					<Loader2 className="w-8 h-8 animate-spin text-blue-500" />
				</div>
			</div>
		);
	}

	// Error state
	if (error) {
		return (
			<div className="frs-discussion-feed">
				{showHeader && (
					<div className="flex items-center justify-between mb-4">
						<h2 className="text-xl font-bold text-gray-900">{title}</h2>
					</div>
				)}
				<div className="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
					<p className="text-red-600 mb-2">{error}</p>
					<button
						onClick={refresh}
						className="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
					>
						<RefreshCw className="w-4 h-4" />
						Try Again
					</button>
				</div>
			</div>
		);
	}

	// Empty state
	if (posts.length === 0) {
		return (
			<div className="frs-discussion-feed">
				{showHeader && (
					<div className="flex items-center justify-between mb-4">
						<h2 className="text-xl font-bold text-gray-900">{title}</h2>
					</div>
				)}
				<div className="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
					<MessageCircle className="w-12 h-12 text-gray-400 mx-auto mb-3" />
					<p className="text-gray-600">No discussions yet</p>
				</div>
			</div>
		);
	}

	return (
		<div className="frs-discussion-feed">
			{/* Header */}
			{showHeader && (
				<div className="flex items-center justify-between mb-4">
					<h2 className="text-xl font-bold text-gray-900">{title}</h2>
					<div className="flex items-center gap-2">
						<span className="text-sm text-gray-500">
							{totalPosts} discussion{totalPosts !== 1 ? 's' : ''}
						</span>
						<button
							onClick={refresh}
							disabled={loading}
							className="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors disabled:opacity-50"
							title="Refresh"
						>
							<RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
						</button>
					</div>
				</div>
			)}

			{/* Posts Grid/List */}
			<div
				className={`
				${layout === 'cards' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4' : ''}
				${layout === 'list' ? 'space-y-4' : ''}
				${layout === 'compact' ? 'space-y-2' : ''}
			`}
			>
				{posts.map((post) => (
					<PostCard key={post.id} post={post} />
				))}
			</div>

			{/* Load More */}
			{hasMore && (
				<div className="mt-6 text-center">
					<button
						onClick={loadMore}
						disabled={loading}
						className="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
					>
						{loading ? (
							<>
								<Loader2 className="w-4 h-4 animate-spin" />
								Loading...
							</>
						) : (
							'Load More'
						)}
					</button>
				</div>
			)}
		</div>
	);
}
