<!-- WHO Growth Standards Content -->
<div class="ajax-content-wrapper">
    <!-- WHO Standards Header -->
    <div class="ajax-section-header" style="background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
        <div class="header-icon-large" style="width: 3rem; height: 3rem; background: rgba(255, 255, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; backdrop-filter: blur(10px);">
            <i class="fas fa-chart-bar"></i>
        </div>
        <h2 style="margin: 0 0 0.5rem 0;">WHO Growth Standards</h2>
        <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Comprehensive reference data for accurate child growth assessment and malnutrition detection</p>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.2);">
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">244</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Total Records</div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">4</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Standards</div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">0-60</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Months Range</div>
            </div>
        </div>
    </div>

    <!-- Growth Standards Dataset -->
    <div class="ajax-section">
        <div class="section-title-bar" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
            <i class="fas fa-database" style="width: 2rem; height: 2rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem;"></i>
            <div>
                <h3 style="margin: 0; font-size: 1.1rem; color: #1f2937;">Growth Standards Dataset</h3>
                <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">Interactive reference data for precise malnutrition assessment calculations</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Male WFA Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem;">
                        <i class="fas fa-mars"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; color: #1f2937; font-weight: 600;">Male - Weight for Age</h4>
                        <span style="font-size: 0.7rem; color: #6b7280; display: inline-block; background: #f0fdf4; padding: 0.25rem 0.5rem; border-radius: 8px; margin-top: 0.25rem;">WFA</span>
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: #6b7280; margin: 0 0 1rem 0;">Underweight assessment reference data</p>
                <div style="display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">{{ count($maleWfa ?? []) ?? '61' }}</div>
                        <div style="color: #6b7280;">Records</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">0-60</div>
                        <div style="color: #6b7280;">Months</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">2024</div>
                        <div style="color: #6b7280;">Updated</div>
                    </div>
                </div>
                <button onclick="viewDataset('male_wfa', 'Male - Weight for Age')" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-table"></i> View Dataset
                </button>
            </div>

            <!-- Female WFA Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem;">
                        <i class="fas fa-venus"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; color: #1f2937; font-weight: 600;">Female - Weight for Age</h4>
                        <span style="font-size: 0.7rem; color: #6b7280; display: inline-block; background: #f0fdf4; padding: 0.25rem 0.5rem; border-radius: 8px; margin-top: 0.25rem;">WFA</span>
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: #6b7280; margin: 0 0 1rem 0;">Underweight assessment reference data</p>
                <div style="display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">{{ count($femaleWfa ?? []) ?? '61' }}</div>
                        <div style="color: #6b7280;">Records</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">0-60</div>
                        <div style="color: #6b7280;">Months</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">2024</div>
                        <div style="color: #6b7280;">Updated</div>
                    </div>
                </div>
                <button onclick="viewDataset('female_wfa', 'Female - Weight for Age')" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-table"></i> View Dataset
                </button>
            </div>

            <!-- Male LHFA Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem;">
                        <i class="fas fa-mars"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; color: #1f2937; font-weight: 600;">Male - Length/Height for Age</h4>
                        <span style="font-size: 0.7rem; color: #6b7280; display: inline-block; background: #f0fdf4; padding: 0.25rem 0.5rem; border-radius: 8px; margin-top: 0.25rem;">LHFA</span>
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: #6b7280; margin: 0 0 1rem 0;">Stunting assessment reference data</p>
                <div style="display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">{{ count($maleLhfa ?? []) ?? '61' }}</div>
                        <div style="color: #6b7280;">Records</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">0-60</div>
                        <div style="color: #6b7280;">Months</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">2024</div>
                        <div style="color: #6b7280;">Updated</div>
                    </div>
                </div>
                <button onclick="viewDataset('male_lhfa', 'Male - Length/Height for Age')" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-table"></i> View Dataset
                </button>
            </div>

            <!-- Female LHFA Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; transition: all 0.3s;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem;">
                        <i class="fas fa-venus"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; color: #1f2937; font-weight: 600;">Female - Length/Height for Age</h4>
                        <span style="font-size: 0.7rem; color: #6b7280; display: inline-block; background: #f0fdf4; padding: 0.25rem 0.5rem; border-radius: 8px; margin-top: 0.25rem;">LHFA</span>
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: #6b7280; margin: 0 0 1rem 0;">Stunting assessment reference data</p>
                <div style="display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">{{ count($femaleLhfa ?? []) ?? '61' }}</div>
                        <div style="color: #6b7280;">Records</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">0-60</div>
                        <div style="color: #6b7280;">Months</div>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-weight: 700; color: #2e7d32;">2024</div>
                        <div style="color: #6b7280;">Updated</div>
                    </div>
                </div>
                <button onclick="viewDataset('female_lhfa', 'Female - Length/Height for Age')" style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-table"></i> View Dataset
                </button>
            </div>
        </div>
    </div>

    <!-- Understanding WHO Standards Info -->
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem;">
        <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1rem; font-weight: 600;">Understanding WHO Standards</h3>
                <p style="margin: 0; color: #6b7280; line-height: 1.5; font-size: 0.9rem;">WHO growth standards are based on comprehensive growth surveys conducted internationally. These standards provide reference data for assessing child growth and nutritional status. The standards include measurements for weight, height/length, and other anthropometric indicators across different age groups and genders.</p>
            </div>
        </div>
    </div>
</div>

<style>
.ajax-content-wrapper {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
