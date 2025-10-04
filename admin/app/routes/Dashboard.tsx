import React from 'react';

/**
 * Main dashboard component for KI Kraft admin.
 */
const Dashboard: React.FC = () => {
	return (
		<div className="ki-kraft-dashboard">
			<h1>KI Kraft Dashboard</h1>
			<div className="dashboard-grid">
				<div className="dashboard-card">
					<h2>Overview</h2>
					<p>Welcome to KI Kraft admin panel.</p>
				</div>
				<div className="dashboard-card">
					<h2>Quick Stats</h2>
					<p>TODO: Add analytics here</p>
				</div>
			</div>
		</div>
	);
};

export default Dashboard;
