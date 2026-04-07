<!-- Treatment Protocols Content -->
<div class="ajax-content-wrapper">
    <!-- Treatment Protocols Header -->
    <div class="ajax-section-header" style="background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); color: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
        <div class="header-icon-large" style="width: 3rem; height: 3rem; background: rgba(255, 255, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; backdrop-filter: blur(10px);">
            <i class="fas fa-file-medical-alt"></i>
        </div>
        <h2 style="margin: 0 0 0.5rem 0;">Clinical Treatment Protocols</h2>
        <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Evidence-based medical interventions for malnutrition management following WHO guidelines</p>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.2);">
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">{{ isset($protocols['protocols']['protocols']) ? count($protocols['protocols']['protocols']) : '4' }}</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Treatment Protocols</div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">0-60</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Age Coverage</div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 700;">WHO</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">Evidence Standard</div>
            </div>
        </div>
    </div>

    <!-- Available Protocols Overview -->
    <div class="ajax-section" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <!-- Total Protocols Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; border-top: 4px solid #2e7d32;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <i style="font-size: 1.75rem; color: #2e7d32; width: 2.5rem; height: 2.5rem; background: #f0fdf4; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📋</i>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ isset($protocols['protocols']['protocols']) ? count($protocols['protocols']['protocols']) : '4' }}</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Treatment Protocols</div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; background: #f0fdf4; color: #047857; padding: 0.35rem 0.75rem; border-radius: 15px; display: inline-block; font-weight: 600;">✓ Active</div>
            </div>

            <!-- Age Coverage Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; border-top: 4px solid #66bb6a;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <i style="font-size: 1.75rem; color: #66bb6a; width: 2.5rem; height: 2.5rem; background: #f0fdf4; border-radius: 8px; display: flex; align-items: center; justify-content: center;">👶</i>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">0-60</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Age Coverage (Months)</div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; background: #f0fdf4; color: #10b981; padding: 0.35rem 0.75rem; border-radius: 15px; display: inline-block; font-weight: 600;">✓ Complete</div>
            </div>

            <!-- Evidence Standard Card -->
            <div style="background: white; border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem; border-top: 4px solid #43a047;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <i style="font-size: 1.75rem; color: #43a047; width: 2.5rem; height: 2.5rem; background: #f0fdf4; border-radius: 8px; display: flex; align-items: center; justify-content: center;">🏥</i>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">WHO</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Evidence Standard</div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; background: #f0fdf4; color: #047857; padding: 0.35rem 0.75rem; border-radius: 15px; display: inline-block; font-weight: 600;">✓ Verified</div>
            </div>

            <!-- Last Updated Card -->
            <div style="background: white; border: 1px solid #fef3c7; border-radius: 10px; padding: 1.5rem; border-top: 4px solid #f59e0b;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <i style="font-size: 1.75rem; color: #f59e0b; width: 2.5rem; height: 2.5rem; background: #fffbeb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📅</i>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">2024</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Last Updated</div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; background: #fffbeb; color: #b45309; padding: 0.35rem 0.75rem; border-radius: 15px; display: inline-block; font-weight: 600;">✓ Current</div>
            </div>
        </div>
    </div>

    <!-- Clinical Protocols List -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="margin: 0 0 1.5rem 0; font-size: 1.1rem; color: #1f2937; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
            <i style="width: 2rem; height: 2rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">📑</i>
            Clinical Protocols
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            @if(isset($protocols['protocols']['protocols']))
                @foreach($protocols['protocols']['protocols'] as $index => $protocolName)
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #d1fae5; border-radius: 10px; padding: 1.25rem; text-align: center; opacity: 0.9;">
                    <div style="font-size: 1.75rem; margin-bottom: 0.5rem;">
                        @if($index === 0)
                            🩺
                        @elseif($index === 1)
                            👥
                        @elseif($index === 2)
                            🏥
                        @else
                            📋
                        @endif
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #1f2937; font-weight: 600;">{{ ucwords(str_replace('_', ' ', $protocolName)) }}</h4>
                    <p style="margin: 0; font-size: 0.75rem; color: #6b7280;">
                        {{ $index === 0 ? 'Severe Acute' : ($index === 1 ? 'Moderate Acute' : 'Standard Care') }}
                    </p>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Detailed Protocol Information -->
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #d1fae5; border-radius: 10px; padding: 1.5rem;">
        <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <div style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                <i class="fas fa-stethoscope"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 1rem; font-weight: 600;">Protocol Framework</h3>
                <div style="color: #6b7280; line-height: 1.6; font-size: 0.9rem;">
                    <p style="margin: 0 0 0.75rem 0;"><strong>WHO Standard Protocol:</strong> Severe acute malnutrition (SAM) treatment with emergency stabilization and intensive therapeutic feeding</p>
                    <p style="margin: 0 0 0.75rem 0;"><strong>Community Based Protocol:</strong> Moderate acute malnutrition (MAM) management through supplementary feeding and community care</p>
                    <p style="margin: 0;"><strong>Summary Protocol:</strong> Normal growth monitoring and preventive nutrition guidelines for children with adequate nutritional status</p>
                </div>
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
